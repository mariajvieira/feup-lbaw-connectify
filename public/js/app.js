document.addEventListener('DOMContentLoaded', function() {
  addEventListeners();
  addReactionEventListeners();
  addCommentEventListeners(); // Nova função para adicionar os eventos de comentários

  document.querySelectorAll('.add-comment-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        sendCreateCommentRequest(event);
    });
  });
});

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
  const reactionButtons = document.querySelectorAll('.reactions button');
  reactionButtons.forEach(button => {
    button.addEventListener('click', react);
  });
}

function react(event) {
  const button = event.target;
  const reactionType = button.getAttribute('data-reaction-type');
  const postId = button.getAttribute('data-post-id');
  const reactionId = button.getAttribute('data-reaction-id'); // Obter o ID da reação, se houver

  if (reactionId) {
    if (button.classList.contains('selected')) {
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
            button.classList.remove('selected');
            button.removeAttribute('data-reaction-id');
            alert(data.message);
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
            const parentReactions = button.closest('.reactions');
            const buttons = parentReactions.querySelectorAll('button');
            buttons.forEach(btn => btn.classList.remove('selected'));
            button.classList.add('selected');
            button.setAttribute('data-reaction-id', data.reaction_id); // Atualizar o ID da reação
            alert(data.message);
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
          const parentReactions = button.closest('.reactions');
          const buttons = parentReactions.querySelectorAll('button');
          buttons.forEach(btn => btn.classList.remove('selected'));
          button.classList.add('selected');
          button.setAttribute('data-reaction-id', data.reaction_id); // Atualizar o ID da reação
          alert(data.message);
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
  const form = event.target;
  const postId = form.getAttribute('data-post-id');
  const commentContent = form.querySelector('textarea[name=comment]').value;

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
  newComment.classList.add('comment', 'mt-2');
  newComment.setAttribute('data-id', comment.id);

  newComment.innerHTML = `
      <p><strong>${comment.user.username}</strong>: ${comment.comment_content}</p>
      <button class="delete-comment-btn" data-id="${comment.id}">Delete</button>
  `;
  commentsContainer.appendChild(newComment);

  // Limpar o campo de comentário
  form.querySelector('textarea[name="comment"]').value = '';

  newComment.querySelector('.delete-comment-btn').addEventListener('click', sendDeleteCommentRequest);
}

function sendDeleteCommentRequest(event) {
  event.preventDefault(); // Prevents the default form submission

  const commentId = event.target.getAttribute('data-id');
  const formAction = `/comment/${commentId}`;

  fetch(formAction, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ comment_id: commentId }),
    redirect: 'manual' // Prevents the redirection
  })
  .then(response => {
    if (response.ok) {
      return response.json();
    } else {
      throw new Error('Erro ao excluir o comentário');
    }
  })
  .then(data => {
    if (data.message === 'Comentário excluído com sucesso.') {
      const commentElement = document.querySelector(`.comment[data-id="${commentId}"]`);
      if (commentElement) {
        commentElement.remove(); // Remove the comment from the page
      }
      alert(data.message); // Show success message
    } else {
      console.error('Erro ao excluir o comentário');
    }
  })
  .catch(error => {
    console.error('Erro ao excluir o comentário:', error);
  });
}



function commentDeletedHandler() {
  if (this.status !== 200) {
      console.error('Erro ao deletar o comentário');
      return;
  }

  const comment = JSON.parse(this.responseText);
  const commentElement = document.querySelector(`.comment[data-id="${comment.id}"]`);
  commentElement.remove();
}


function sendAjaxRequest(method, url, data, handler) {
  let request = new XMLHttpRequest();
  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  request.setRequestHeader('Content-Type', 'application/json');
  request.addEventListener('load', handler);
  request.send(JSON.stringify(data));
}
