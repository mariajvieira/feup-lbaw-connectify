document.addEventListener('DOMContentLoaded', function() {
  addReactionEventListeners();
});

window.addEventListener('DOMContentLoaded', function () {
  var header = document.querySelector('header');
  var mainContent = document.querySelector('main');
  var headerHeight = header.offsetHeight;
  mainContent.style.paddingTop = headerHeight + 'px';
});

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.save-post-btn').forEach(button => {
      button.addEventListener('click', function() {
          const postId = button.getAttribute('data-post-id');
          const isSaved = button.getAttribute('data-saved') === 'true';

          if (isSaved) {
              removeSavePost(postId, button);
          } else {
              savePost(postId, button);
          }
      });
  });
});

// Função para salvar o post
function savePost(postId, button) {
  fetch('/save-post', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ post_id: postId })
  })
  .then(response => response.json())
  .then(data => {
      if (data.saved) {
          button.innerHTML = '<i class="fa-solid fa-bookmark"></i> Saved';
          button.setAttribute('data-saved', 'true');
      }
  })
  .catch(error => {
      console.error('Erro ao salvar o post:', error);
  });
}

// Função para remover o post salvo
function removeSavePost(postId, button) {
  fetch('/remove-save-post', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ post_id: postId })
  })
  .then(response => response.json())
  .then(data => {
      if (!data.saved) {
          button.innerHTML = '<i class="fa-regular fa-bookmark"></i> Save';
          button.setAttribute('data-saved', 'false');
      }
  })
  .catch(error => {
      console.error('Erro ao remover o post salvo:', error);
  });
}




function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data)
      .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(data[k])}`)
      .join('&');
}



function addReactionEventListeners() {
  const reactionButtons = document.querySelectorAll('.reaction-button');
  reactionButtons.forEach(button => {
    button.addEventListener('click', reactPost);
    button.addEventListener('click', reactComment);
  });

}

function reactPost(event) {
  const button = event.target;
  const reactionType = button.getAttribute('data-reaction-type');
  const postId = button.getAttribute('data-post-id');
  let reactionId = button.getAttribute('data-reaction-id'); 

  if (reactionId) {
    if (button.classList.contains('btn-outline-danger')) {
      fetch(`/reaction/${reactionId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(errorData => {
            throw new Error(errorData.error || 'Erro ao apagar a reação');
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.message === 'Reação removida com sucesso.') {
          button.classList.remove('btn-outline-danger');
          button.classList.add('btn-outline-secondary');
          button.removeAttribute('data-reaction-id');
          updatePostReactionCount(postId);
        } else {
          console.error('Erro ao apagar a reação:', data.error);
        }
      })
      .catch(error => {
        console.error('Erro ao apagar a reação:', error);
      });
    } else {
      fetch(`/post/${postId}/reaction`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
          target_id: postId,
          target_type: 'post',
          reaction_type: reactionType,
        }),
      })
      .then(response => response.json())
      .then(data => {
        if (data.message === 'Reação registada com sucesso.') {
          const parentReactions = button.closest('.d-flex'); 
          buttons.forEach(btn => {
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-outline-secondary');
          });
          button.classList.remove('btn-outline-secondary');
          button.classList.add('btn-outline-danger');
          button.setAttribute('data-reaction-id', data.reaction_id);
          updatePostReactionCount(postId);
        } else {
          console.error('Erro ao registar a reação:', data.error);
        }
      })
      .catch(error => console.error('Erro ao registar a reação:', error));
    }
  } else {
    fetch(`/post/${postId}/reaction`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({
        target_id: postId,
        target_type: 'post',
        reaction_type: reactionType,
      }),
    })
    .then(response => response.json())
    .then(data => {
      if (data.message === 'Reação registada com sucesso.') {
        const parentReactions = button.closest('.d-flex'); 
        const buttons = parentReactions.querySelectorAll('button');
        buttons.forEach(btn => {
          btn.classList.remove('btn-outline-danger');
          btn.classList.add('btn-outline-secondary');
        });
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-outline-danger');
        button.setAttribute('data-reaction-id', data.reaction_id); 


        updatePostReactionCount(postId);
      } else {
        console.error('Erro ao registar a reação:', data.error);
      }
    })
    .catch(error => console.error('Erro ao registar a reação:', error));
  }
}


function reactComment(event) {
  const button = event.target;
  const reactionType = button.getAttribute('data-reaction-type');
  const commentId = button.getAttribute('data-comment-id');
  let reactionId = button.getAttribute('data-reaction-id'); 

  if (reactionId) {
    if (button.classList.contains('btn-outline-danger')) {
      fetch(`/reaction/${reactionId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(errorData => {
            throw new Error(errorData.error || 'Erro ao apagar a reação');
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.message === 'Reação removida com sucesso.') {
          button.classList.remove('btn-outline-danger');
          button.classList.add('btn-outline-secondary');
          button.removeAttribute('data-reaction-id');

          updateCommentReactionCount(commentId);
        } else {
          console.error('Erro ao apagar a reação:', data.error);
        }
      })
      .catch(error => {
        console.error('Erro ao apagar a reação:', error);
      });
    } else {
      fetch(`/comment/${commentId}/reaction`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
          target_id: commentId,
          target_type: 'comment',
          reaction_type: reactionType,
        }),
      })
      .then(response => response.json())
      .then(data => {
        if (data.message === 'Reação registada com sucesso.') {
          const parentReactions = button.closest('.d-flex'); 
          buttons.forEach(btn => {
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-outline-secondary');
          });
          button.classList.remove('btn-outline-secondary');
          button.classList.add('btn-outline-danger');
          button.setAttribute('data-reaction-id', data.reaction_id);

          updateCommentReactionCount(commentId);
        } else {
          console.error('Erro ao registar a reação:', data.error);
        }
      })
      .catch(error => console.error('Erro ao registar a reação:', error));
    }
  } else {
    fetch(`/comment/${commentId}/reaction`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({
        target_id: commentId,
        target_type: 'comment',
        reaction_type: reactionType,
      }),
    })
    .then(response => response.json())
    .then(data => {
      if (data.message === 'Reação registada com sucesso.') {
        const parentReactions = button.closest('.d-flex'); 
        const buttons = parentReactions.querySelectorAll('button');
        buttons.forEach(btn => {
          btn.classList.remove('btn-outline-danger');
          btn.classList.add('btn-outline-secondary');
        });
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-outline-danger');
        button.setAttribute('data-reaction-id', data.reaction_id); 

        updateCommentReactionCount(commentId);
      } else {
        console.error('Erro ao registar a reação:', data.error);
      }
    })
    .catch(error => console.error('Erro ao registar a reação:', error));
  }
}



function updatePostReactionCount(postId) {
  fetch(`/post/${postId}/reactions/count`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })
  .then(response => response.json())
  .then(data => {
    if (data && data.reactionCount !== undefined) {
      const reactionCountElement = document.getElementById('reaction-count-' + postId);
      if (reactionCountElement) {
        const reactionText = data.reactionCount + ' ' + (data.reactionCount === 1 ? 'reaction' : 'reactions');
        const reactionLink = reactionCountElement.querySelector('a');

        reactionCountElement.textContent = reactionText;

        if (reactionLink) {
          reactionLink.setAttribute('href', `/post/${postId}/reactions`);
        }
      }
    } else {
      console.error('Erro ao atualizar a contagem de reações:', data.error || 'Erro desconhecido');
    }
  })
  .catch(error => {
    console.error('Erro ao realizar a requisição para contar as reações:', error);
  });
}



function updateCommentReactionCount(commentId) {
  fetch(`/comment/${commentId}/reactions/count`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })
  .then(response => response.json())
  .then(data => {
    if (data && data.reactionCount !== undefined) {
      const reactionCountElement = document.getElementById('reaction-count-' + commentId);
      if (reactionCountElement) {
        // Atualiza a contagem de reações
        reactionCountElement.textContent = data.reactionCount + ' ' + (data.reactionCount === 1 ? 'reaction' : 'reactions');
      }
    } else {
      console.error('Erro ao atualizar a contagem de reações:', data.error || 'Erro desconhecido');
    }
  })
  .catch(error => {
    console.error('Erro ao realizar a requisição para contar as reações:', error);
  });
}


document.addEventListener('DOMContentLoaded', function () {
  const commentForms = document.querySelectorAll('.add-comment-form');
  commentForms.forEach(function (form) {
    form.addEventListener('submit', postComment);
  });
});

function postComment(e) {
  e.preventDefault(); 

  const form = e.target;
  const postId = form.dataset.postId;
  const commentContent = form.querySelector('#comment').value;

  if (commentContent.trim() === '') {
    alert('Please enter a comment.');
    return;
  }

  const xhr = new XMLHttpRequest();
  xhr.open('POST', `/post/${postId}/comment`, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

  const queryString = `comment=${encodeURIComponent(commentContent)}&_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').getAttribute('content'))}`;

  xhr.onload = function () {
    if (xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);
      
      if (response.comment) {
        form.querySelector('#comment').value = '';

        const commentListContainer = document.querySelector('.comment-section .comments-list');
        const commentHtml = `
          <div class="container mt-5">
            <div class="comments-list mt-2" id="comment-${response.comment.id}">
              <div class="d-flex justify-content-between align-items-center">
                <span>
                  <strong>
                    <a class="text-custom text-decoration-none" href="/user/${response.comment.user.id}">
                      ${response.comment.user.username}
                    </a>
                  </strong>: 
                  <span class="comment-text">${response.comment.comment_content}</span>
                </span>

                ${response.comment.can_destroy ? `
                <div class="post-actions d-flex align-items-center gap-2">
                  <a href="javascript:void(0)" class="btn btn-custom edit-comment" data-comment-id="${response.comment.id}">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <form class="delete-comment-form mb-0" action="/comment/${response.comment.id}" method="POST" style="display: inline;">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-2 delete-comment">
                      <i class="fa-solid fa-trash"></i> 
                    </button>
                  </form>
                </div>
                ` : ''}
              </div>
            </div>
          </div>
        `;
        commentListContainer.insertAdjacentHTML('beforeend', commentHtml);

        alert('The comment has been posted successfully.');
      } else {
        alert('Failed to post comment.');
      }
    } else {
      alert('Error posting comment.');
    }
  };

  xhr.send(queryString);
}

// Função para atualizar a lista de comentários
function updateCommentList(postId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/post/${postId}/comments`, true);  

  xhr.onload = function () {
    if (xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);
      
      if (response.comments) {
        const commentListContainer = document.querySelector('.comment-section .comments-list');
        
        if(commentListContainer) {
        commentListContainer.innerHTML = '';

        response.comments.forEach(comment => {
          const commentHtml = `
          <div class="container mt-5">
            <div class="comments-list mt-2" id="comment-${response.comment.id}">
              <div class="d-flex justify-content-between align-items-center">
                <span>
                  <strong>
                    <a class="text-custom text-decoration-none" href="/user/${response.comment.user.id}">
                      ${response.comment.user.username}
                    </a>
                  </strong>: 
                  <span class="comment-text">${response.comment.comment_content}</span>
                </span>

                ${response.comment.can_destroy ? `
                <div class="post-actions d-flex align-items-center gap-2">
                  <a href="javascript:void(0)" class="btn btn-custom edit-comment" data-comment-id="${response.comment.id}">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <form class="delete-comment-form mb-0" action="/comment/${response.comment.id}" method="POST" style="display: inline;">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-2 delete-comment">
                      <i class="fa-solid fa-trash"></i> 
                    </button>
                  </form>
                </div>
                ` : ''}
              </div>
            </div>
          </div>
        `;
          commentListContainer.insertAdjacentHTML('beforeend', commentHtml);
        });
        } else {
          console.error('Elemento .comments-list não encontrado.');
        }
      } else {
        console.error('Erro ao obter os comentários: resposta não contém a chave "comments".');
      }
    } else {
      console.error('Erro ao carregar os comentários. Status:', xhr.status);
    }
  };

  xhr.send();
}


// Exclusão de comentário
document.addEventListener('click', function(e) {
  if (e.target && e.target.classList.contains('delete-comment')) {
    e.preventDefault();
    
    var confirmed = confirm('Are you sure you want to delete this comment? This action cannot be undone.');
    if (confirmed) {
      var form = e.target.closest('form');
      var formData = new FormData(form);
      var queryString = new URLSearchParams(formData).toString();
      
      fetch(form.action, {
        method: form.method,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: queryString
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error excluding comment.');
        }
        return response.json();
      })
      .then(data => {
        var commentElement = form.closest('.comments-list');
        if (commentElement) {
          commentElement.remove();
        }
        alert('The comment has been excluded successfully.');
      })
      .catch(error => {
        alert(error.message);
      });
    }
  }
});




// Edição de comentário
document.addEventListener('click', function (e) {
  if (e.target && e.target.classList.contains('edit-comment')) {
    const button = e.target;
    const commentId = button.dataset.commentId;
    const commentElement = button.closest('.comment');
    const commentTextElement = commentElement.querySelector('.comment-text');
    const originalComment = commentTextElement.textContent.trim();
    
    const editField = `<input type="text" class="form-control edit-comment-field" value="${originalComment}" />`;
    commentTextElement.innerHTML = editField;

    button.innerHTML = '<i class="fa-solid fa-save"></i>';
    button.classList.remove('edit-comment');
    button.classList.add('save-comment');
  }
});

// Salvar edição do comentário
document.addEventListener('click', function (e) {
  if (e.target && e.target.classList.contains('save-comment')) {
    const button = e.target;
    const commentId = button.dataset.commentId;
    const commentElement = button.closest('.comment');
    const commentTextElement = commentElement.querySelector('.comment-text');
    const editField = commentTextElement.querySelector('.edit-comment-field');
    const updatedComment = editField.value.trim();
    
    // Verifica se o comentário não está vazio
    if (updatedComment === '') {
      alert('O comentário não pode ser vazio.');
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('PUT', `/comments/${commentId}`, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    // Envia os dados para o servidor
    const formData = new URLSearchParams();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('content', updatedComment);

    xhr.onload = function () {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        // Atualiza o conteúdo no DOM com a resposta
        commentTextElement.innerHTML = response.content;
        button.innerHTML = '<i class="fa-solid fa-pen"></i>';
        button.classList.remove('save-comment');
        button.classList.add('edit-comment');
      } else {
        alert('Erro ao salvar o comentário.');
        commentTextElement.innerHTML = originalComment;
      }
    };

    xhr.send(formData.toString());
  }
});

// JOIN GROUP
document.addEventListener('DOMContentLoaded', function () {
  // Verifica se o botão "Join this Public Group" existe
  const joinButton = document.getElementById('join-group');

  if (joinButton) {
      joinButton.addEventListener('click', function () {
          const groupId = joinButton.getAttribute('data-group-id');
          
          // Envia a requisição AJAX
          fetch(`/groups/${groupId}/join`, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                  group_id: groupId
              })
          })
          .then(response => response.json())
          .then(data => {
              if (data.message === 'Successfully joined the group!') {
                  alert('You have successfully joined the group!');
                  location.reload();  
              } else {
                  alert(data.message);  
              }
          })
          .catch(error => {
              console.error('Error:', error);
              alert('An error occurred while trying to join the group.');
          });
      });
  }
});

// FRIENDSHIPS
document.addEventListener('DOMContentLoaded', function () {
    const userId = document.querySelector('meta[name="user-id"]').getAttribute('content'); // Obtém o ID do usuário autenticado
    const friendsList = document.getElementById('friends-list');

    // Função para buscar amigos do usuário
    function fetchFriends() {
        fetch(`/user/${userId}/friends`)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar a lista de amigos.');
                return response.json();
            })
            .then(friends => {
                
                friendsList.innerHTML = '';

                if (friends.length === 0) {
                    friendsList.innerHTML = '<p>You have no friends yet</p>';
                    return;
                }
                console.log(friends)
                // Adiciona cada amigo na lista
                friends.forEach(friend => {
                    const listItem = document.createElement('li');
                    listItem.id = `friend-${friend.id}`;
                    listItem.innerHTML = `
                        <span>${friend.username}</span>
                        <button class="btn btn-danger btn-sm remove-btn" data-id="${friend.id}">
                            Remove
                        </button>
                    `;
                    friendsList.appendChild(listItem);
                });

                // Adiciona evento aos botões de remover
                document.querySelectorAll('.remove-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const friendId = this.dataset.id;
                        
                        removeFriend(friendId);
                    });
                });
            })
            .catch(error => console.error('Erro:', error));
    }

    // Função para remover um amigo
    function removeFriend(friendId) {
        fetch(`/friendship/remove/${friendId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                receiver_id: friendId 
            })
        })
            .then(response => {
                if (!response.ok) throw new Error('Erro ao remover amigo.');
                return response.json();
            })
            .then(data => {
                console.log(data.message);
                const friendItem = document.getElementById(`friend-${friendId}`);
                if (friendItem) friendItem.remove();
            })
            .catch(error => console.error('Erro:', error));
    }

    // Carrega a lista de amigos ao carregar a página
    fetchFriends();
});


// DELETE ACCOUNT
document.addEventListener('DOMContentLoaded', function () {
  const deleteAccountBtn = document.getElementById('deleteAccountBtn');
  const confirmDeleteModal = document.getElementById('confirmDeleteModal');
  const cancelBtn = document.getElementById('cancelBtn');
  const closeModalBtn = document.getElementById('closeModalBtn');
  
  function openModal() {
      confirmDeleteModal.classList.add('show'); 
      confirmDeleteModal.style.display = 'block'; 
  }

  function closeModal() {
      confirmDeleteModal.classList.remove('show'); 
      confirmDeleteModal.style.display = 'none'; 
  }

  deleteAccountBtn.addEventListener('click', openModal);

  cancelBtn.addEventListener('click', closeModal);

  closeModalBtn.addEventListener('click', closeModal);
});



