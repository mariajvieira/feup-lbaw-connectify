document.addEventListener('DOMContentLoaded', function() {
  addEventListeners();
  addReactionEventListeners();
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
}

function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data)
      .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(data[k])}`)
      .join('&');
}

function sendAjaxRequest(method, url, data, handler) {
  let request = new XMLHttpRequest();
  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  request.addEventListener('load', handler);
  request.send(encodeForAjax(data));
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
  `;

  newItem.querySelector('input[type=checkbox]').addEventListener('change', sendPostUpdateRequest);
  newItem.querySelector('.delete-post-btn').addEventListener('click', sendDeletePostRequest);

  // Add reaction event listeners for new buttons
  addReactionEventListeners();

  return newItem;
}

function react(event) {
  const button = event.target;
  const reactionType = button.getAttribute('data-reaction-type');
  const postId = button.getAttribute('data-post-id');

  // Enviar nova reação ao servidor
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
.then(response => {
    if (!response.ok) {
        return response.text(); // Se não for ok, retorne a resposta como texto
    }
    return response.json(); // Caso contrário, analise como JSON
})
.then(data => {
    if (typeof data === 'string') {
        console.error('Erro na resposta do servidor:', data);
    } else {
        if (data.message === 'Reação registada com sucesso.') {
            const parentReactions = button.closest('.reactions');
            const buttons = parentReactions.querySelectorAll('button');
            buttons.forEach(btn => btn.classList.remove('selected'));
            button.classList.add('selected');
        } else {
            console.error('Erro ao registar a reação:', data.error);
        }
    }
})
.catch(error => console.error('Erro ao registar a reação:', error));

}

function addReactionEventListeners() {
  const reactionButtons = document.querySelectorAll('.reactions button');
  reactionButtons.forEach(button => button.addEventListener('click', react));
}
