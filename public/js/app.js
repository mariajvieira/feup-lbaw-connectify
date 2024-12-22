document.addEventListener('DOMContentLoaded', function() {
  addEventListeners();
  addReactionEventListeners();
  addCommentEventListeners(); 

  document.querySelectorAll('.add-comment-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        sendCreateCommentRequest(event);
    });
  });
});

window.addEventListener('DOMContentLoaded', function () {
  var header = document.querySelector('header');
  var mainContent = document.querySelector('main');
  var headerHeight = header.offsetHeight;
  mainContent.style.paddingTop = headerHeight + 'px';
});

document.addEventListener('DOMContentLoaded', function() {
  // Adicionar eventos aos botões de salvar
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


function addEventListeners() {
  let postCheckers = document.querySelectorAll('.post-item input[type=checkbox]');
  postCheckers.forEach(checker => {
      checker.addEventListener('change', sendPostUpdateRequest);
  });

  let postCreators = document.querySelectorAll('button.new-post-button');
  postCreators.forEach(creator => {
      creator.addEventListener('click', function(event) {
          event.preventDefault();
          sendCreatePostRequest();
      });
  });

  let postDeleters = document.querySelectorAll('.post-item .delete-post-btn');
  postDeleters.forEach(deleter => {
      deleter.addEventListener('click', sendDeletePostRequest);
  });

  let commentDeleters = document.querySelectorAll('.delete-comment-btn');
  commentDeleters.forEach(deleter => {
      deleter.addEventListener('click', sendDeleteCommentRequest);
  });
}

function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data)
      .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(data[k])}`)
      .join('&');
}

function updatePostList(posts) {
  const postsContainer = document.querySelector('.posts-container');
  postsContainer.innerHTML = ''; // Limpa os posts atuais

  posts.forEach(post => {
    const postElement = createPostElement(post);
    postsContainer.appendChild(postElement);
  });
}

function sendPostUpdateRequest() {
  const item = this.closest('.post-item');
  const id = item.getAttribute('data-id');
  const checked = item.querySelector('input[type=checkbox]').checked;

  sendAjaxRequest('POST', `/api/posts/${id}`, { done: checked }, postUpdatedHandler);
}

function sendDeletePostRequest() {
  const id = this.closest('.post-item').getAttribute('data-id');
  sendAjaxRequest('DELETE', `/api/posts/${id}`, null, postDeletedHandler);
}

function sendCreatePostRequest() {
  const form = document.querySelector('.create-post-form');
  const content = form.querySelector('textarea[name=content]').value;
  const isPublic = form.querySelector('select[name=is_public]').value;

  const formData = new FormData();
  formData.append('content', content);
  formData.append('is_public', isPublic);

  const imageFields = ['image1', 'image2', 'image3'];
  imageFields.forEach(field => {
      const imageInput = form.querySelector(`input[name=${field}]`);
      if (imageInput.files.length > 0) {
          formData.append(field, imageInput.files[0]);
      }
  });

  sendAjaxRequest('POST', '/api/posts', formData, postAddedHandler);
}

function postUpdatedHandler() {
  if (this.status !== 200) {
      console.error('Erro ao atualizar o post');
      return;
  }

  const item = JSON.parse(this.responseText);
  const element = document.querySelector(`.post-item[data-id="${item.id}"]`);
  const checkbox = element.querySelector('input[type=checkbox]');
  checkbox.checked = item.done === 'true';
}

function postAddedHandler() {
  if (this.status !== 200) {
      window.location = '/';
      return;
  }

  const item = JSON.parse(this.responseText);
  const newItem = createPostElement(item);

  document.querySelector('.posts-container').prepend(newItem);

  const form = document.querySelector('.create-post-form');
  form.reset(); // Limpar os campos do formulário
}

function postDeletedHandler() {
  if (this.status !== 200) {
      console.error('Erro ao deletar o post');
      return;
  }

  const item = JSON.parse(this.responseText);
  const element = document.querySelector(`.post-item[data-id="${item.id}"]`);
  element.remove();
}

function createPostElement(post) {
  const newItem = document.createElement('div');
  newItem.classList.add('post-item');
  newItem.setAttribute('data-id', post.id);
  newItem.innerHTML = `
      <div class="post-content">
          <input type="checkbox" ${post.done === 'true' ? 'checked' : ''}>
          <span>${post.content}</span>
          <a href="#" class="delete-post-btn">Delete</a>
      </div>
      <div class="reactions mt-3">
          ${['like', 'laugh', 'cry', 'applause', 'shocked'].map(reaction => `
              <button 
                  class="reaction-button ${post.user_reaction === reaction ? 'selected' : ''}" 
                  data-reaction-type="${reaction}" 
                  data-post-id="${post.id}">
                  ${reaction.charAt(0).toUpperCase() + reaction.slice(1)}
              </button>
          `).join('')}
      </div>
      <div class="comments mt-4">
          ${post.comments.map(comment => `
              <div class="comment-item" data-id="${comment.id}">
                  <span>${comment.user.name}: ${comment.content}</span>
                  <button class="delete-comment-btn" data-id="${comment.id}">Delete</button>
              </div>
          `).join('')}
      </div>
      <div class="create-comment">
          <textarea name="comment-content" placeholder="Add a comment..."></textarea>
          <button class="create-comment-btn" data-post-id="${post.id}">Comment</button>
      </div>
  `;

  newItem.querySelector('input[type=checkbox]').addEventListener('change', sendPostUpdateRequest);
  newItem.querySelector('.delete-post-btn').addEventListener('click', sendDeletePostRequest);
  newItem.querySelector('.create-comment-btn').addEventListener('click', sendCreateCommentRequest);

  // Add reaction event listeners for new buttons
  addReactionEventListeners();

  // Add delete comment event listeners
  newItem.querySelectorAll('.delete-comment-btn').forEach(deleter => {
    deleter.addEventListener('click', sendDeleteCommentRequest);
  });

  return newItem;
}

function addReactionEventListeners() {
  const reactionButtons = document.querySelectorAll('.reaction-button');
  reactionButtons.forEach(button => {
    button.addEventListener('click', react);
  });
}

function react(event) {
  const button = event.target;
  const reactionType = button.getAttribute('data-reaction-type');
  const postId = button.getAttribute('data-post-id');
  let reactionId = button.getAttribute('data-reaction-id'); 

  if (reactionId) {
    if (button.classList.contains('btn-outline-danger')) {
      // Caso a mesma reação esteja selecionada, apagá-la
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
        } else {
          console.error('Erro ao apagar a reação:', data.error);
        }
      })
      .catch(error => {
        console.error('Erro ao apagar a reação:', error);
      });
    } else {
      // Caso outra reação seja clicada, substituir a existente
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
          // Remover todas as outras seleções e adicionar a nova
          const parentReactions = button.closest('.d-flex'); // Encontrar o contêiner de reações
          const buttons = parentReactions.querySelectorAll('button');
          buttons.forEach(btn => {
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-outline-secondary');
          });
          button.classList.remove('btn-outline-secondary');
          button.classList.add('btn-outline-danger');
          button.setAttribute('data-reaction-id', data.reaction_id); // Atualizar o ID da reação
        } else {
          console.error('Erro ao registar a reação:', data.error);
        }
      })
      .catch(error => console.error('Erro ao registar a reação:', error));
    }
  } else {
    // Se não existe reação, criar uma nova
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
        // Remover todas as outras seleções e adicionar a nova
        const parentReactions = button.closest('.d-flex'); // Encontrar o contêiner de reações
        const buttons = parentReactions.querySelectorAll('button');
        buttons.forEach(btn => {
          btn.classList.remove('btn-outline-danger');
          btn.classList.add('btn-outline-secondary');
        });
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-outline-danger');
        button.setAttribute('data-reaction-id', data.reaction_id); // Atualizar o ID da reação
      } else {
        console.error('Erro ao registar a reação:', data.error);
      }
    })
    .catch(error => console.error('Erro ao registar a reação:', error));
  }
}

function addCommentEventListeners() {
  document.querySelectorAll('.create-comment-btn').forEach(button => {
    button.addEventListener('click', sendCreateCommentRequest);
  });

  document.querySelectorAll('.delete-comment-btn').forEach(button => {
    button.addEventListener('click', sendDeleteCommentRequest);
  });
}

function sendCreateCommentRequest(event) {
  const form = event.target.closest('.create-comment');
  const postId = form.getAttribute('data-post-id');
  const commentContent = form.querySelector('textarea[name=comment-content]').value;

  const data = {
      comment: commentContent,
      post_id: postId,
  };

  sendAjaxRequest('POST', form.action, data, commentAddedHandler.bind(null, form));
}

function commentAddedHandler(form) {
  if (this.status !== 201) {
      console.error('Erro ao adicionar o comentário');
      return;
  }

  const comment = JSON.parse(this.responseText);
  const commentsContainer = form.closest('.comments');
  const newComment = document.createElement('div');
  newComment.classList.add('comment-item');
  newComment.setAttribute('data-id', comment.id);
  newComment.innerHTML = `
      <span>${comment.user.name}: ${comment.content}</span>
      <button class="delete-comment-btn">Delete</button>
  `;

  commentsContainer.appendChild(newComment);

  newComment.querySelector('.delete-comment-btn').addEventListener('click', sendDeleteCommentRequest);
}

function sendDeleteCommentRequest(event) {
  const commentId = event.target.closest('.comment-item').getAttribute('data-id');
  sendAjaxRequest('DELETE', `/api/comments/${commentId}`, null, commentDeletedHandler);
}

function commentDeletedHandler() {
  if (this.status !== 200) {
      console.error('Erro ao deletar o comentário');
      return;
  }

  const comment = JSON.parse(this.responseText);
  const commentElement = document.querySelector(`.comment-item[data-id="${comment.id}"]`);
  commentElement.remove();
}
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
                  location.reload();  // Atualiza a página para refletir as mudanças
              } else {
                  alert(data.message);  // Exibe a mensagem de erro
              }
          })
          .catch(error => {
              console.error('Error:', error);
              alert('An error occurred while trying to join the group.');
          });
      });
  }
});
// Código JavaScript no app.js

// Espera o DOM estar pronto
document.addEventListener('DOMContentLoaded', function () {
  const deleteAccountBtn = document.getElementById('deleteAccountBtn');
  const confirmDeleteModal = document.getElementById('confirmDeleteModal');
  const cancelBtn = document.getElementById('cancelBtn');
  const closeModalBtn = document.getElementById('closeModalBtn');
  
  // Função para abrir o modal
  function openModal() {
      confirmDeleteModal.classList.add('show'); // Exibe o modal
      confirmDeleteModal.style.display = 'block'; // Exibe o modal (sem jQuery)
  }

  // Função para fechar o modal
  function closeModal() {
      confirmDeleteModal.classList.remove('show'); // Esconde o modal
      confirmDeleteModal.style.display = 'none'; // Esconde o modal (sem jQuery)
  }

  // Adicionando o evento de clique no botão para abrir o modal
  deleteAccountBtn.addEventListener('click', openModal);

  // Adicionando o evento de clique no botão de cancelar para fechar o modal
  cancelBtn.addEventListener('click', closeModal);

  // Adicionando o evento de clique no botão de fechar (X) para fechar o modal
  closeModalBtn.addEventListener('click', closeModal);
});
