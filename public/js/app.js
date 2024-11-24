// Função para adicionar os event listeners aos elementos
function addEventListeners() {
  // Listener para os checkboxes de conclusão de post
  let postCheckers = document.querySelectorAll('.post-item input[type=checkbox]');
  [].forEach.call(postCheckers, function(checker) {
      checker.addEventListener('change', sendPostUpdateRequest);
  });

  // Listener para o formulário de criação de post
  let postCreators = document.querySelectorAll('button.new-post-button');
  [].forEach.call(postCreators, function(creator) {
      creator.addEventListener('click', function(event) {
          event.preventDefault(); // Previne o comportamento padrão de envio do formulário
          sendCreatePostRequest(event); // Chama a função para enviar o post via AJAX
      });
  });

  // Listener para os botões de deletar post
  let postDeleters = document.querySelectorAll('.post-item .delete-post-btn');
  [].forEach.call(postDeleters, function(deleter) {
      deleter.addEventListener('click', sendDeletePostRequest);
  });
}

// Função para codificar os dados para uma requisição AJAX
function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function(k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
  }).join('&');
}

// Função genérica para enviar requisições AJAX
function sendAjaxRequest(method, url, data, handler) {
  let request = new XMLHttpRequest();

  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  request.addEventListener('load', handler);
  request.send(encodeForAjax(data));
}

// Função para atualizar o estado do post (checkbox)
function sendPostUpdateRequest() {
  let item = this.closest('.post-item');
  let id = item.getAttribute('data-id');
  let checked = item.querySelector('input[type=checkbox]').checked;

  sendAjaxRequest('post', '/api/posts/' + id, { done: checked }, postUpdatedHandler);
}

// Função para deletar um post
function sendDeletePostRequest() {
  let id = this.closest('.post-item').getAttribute('data-id');

  sendAjaxRequest('delete', '/api/posts/' + id, null, postDeletedHandler);
}

// Função para criar um post
function sendCreatePostRequest(event) {
    let form = document.querySelector('.create-post-form');
    let content = form.querySelector('textarea[name=content]').value;
    let isPublic = form.querySelector('select[name=is_public]').value;

    let data = {
        content: content,
        is_public: isPublic
    };

    // Envia os dados corretamente via AJAX
    sendAjaxRequest('POST', '/post/store', data, postAddedHandler);
    event.preventDefault();
}


// Manipulador para quando um post for atualizado
function postUpdatedHandler() {
  if (this.status != 200) {
      console.error('Erro ao atualizar o post');
      return;
  }

  let item = JSON.parse(this.responseText);
  let element = document.querySelector('.post-item[data-id="' + item.id + '"]');
  let checkbox = element.querySelector('input[type=checkbox]');
  checkbox.checked = item.done === "true";  // Atualiza o estado do checkbox
}

// Manipulador para quando um post for criado
function postAddedHandler() {
  if (this.status != 200) {
      window.location = '/';
      return;
  }

  let item = JSON.parse(this.responseText);
  let newItem = createPostElement(item);

  // Adiciona o novo post à interface
  document.querySelector('.posts-container').prepend(newItem);

  // Limpa o campo de entrada
  document.querySelector('.create-post-form input[name=content]').value = "";
}

// Manipulador para quando um post for deletado
function postDeletedHandler() {
  if (this.status != 200) {
      window.location = '/';
      return;
  }

  let item = JSON.parse(this.responseText);
  let element = document.querySelector('.post-item[data-id="' + item.id + '"]');
  element.remove();
}

// Função para criar a estrutura HTML de um post
function createPostElement(post) {
  let newItem = document.createElement('div');
  newItem.classList.add('post-item');
  newItem.setAttribute('data-id', post.id);
  newItem.innerHTML = `
      <div class="post-content">
          <input type="checkbox" ${post.done === "true" ? 'checked' : ''}>
          <span>${post.content}</span>
          <a href="#" class="delete-post-btn">Delete</a>
      </div>
  `;

  // Adiciona event listeners para os novos botões
  newItem.querySelector('input[type=checkbox]').addEventListener('change', sendPostUpdateRequest);
  newItem.querySelector('.delete-post-btn').addEventListener('click', sendDeletePostRequest);

  return newItem;
}

// Chama a função para adicionar os event listeners aos posts
addEventListeners();
/*

  // Função para criar um novo post
async function createPost(data) {
  try {
      const response = await fetch('/api/posts', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Necessário para Laravel
          },
          body: JSON.stringify(data)
      });

      if (!response.ok) {
          const error = await response.json();
          console.error('Erro ao criar post:', error);
          alert(error.message || 'Erro ao criar o post.');
          return;
      }

      const result = await response.json();
      console.log('Post criado com sucesso:', result);
      alert('Post criado com sucesso!');
      // Atualize a interface para exibir o novo post
  } catch (err) {
      console.error('Erro de rede:', err);
      alert('Erro ao conectar ao servidor.');
  }
}

// Função para atualizar um post
async function updatePost(postId, data) {
  try {
      const response = await fetch(`/api/posts/${postId}`, {
          method: 'PUT',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(data)
      });

      if (!response.ok) {
          const error = await response.json();
          console.error('Erro ao atualizar post:', error);
          alert(error.message || 'Erro ao atualizar o post.');
          return;
      }

      const result = await response.json();
      console.log('Post atualizado com sucesso:', result);
      alert('Post atualizado com sucesso!');
      // Atualize a interface para refletir as alterações
  } catch (err) {
      console.error('Erro de rede:', err);
      alert('Erro ao conectar ao servidor.');
  }
}

// Função para deletar um post
async function deletePost(postId) {
  if (!confirm('Tem certeza que deseja deletar este post?')) {
      return;
  }

  try {
      const response = await fetch(`/api/posts/${postId}`, {
          method: 'DELETE',
          headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
      });

      if (!response.ok) {
          const error = await response.json();
          console.error('Erro ao deletar post:', error);
          alert(error.message || 'Erro ao deletar o post.');
          return;
      }

      console.log('Post deletado com sucesso!');
      alert('Post deletado com sucesso!');
      // Remova o post da interface
  } catch (err) {
      console.error('Erro de rede:', err);
      alert('Erro ao conectar ao servidor.');
  }
}

// Função para carregar posts
async function loadPosts() {
  try {
      const response = await fetch('/api/posts', {
          method: 'GET',
          headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
      });

      if (!response.ok) {
          const error = await response.json();
          console.error('Erro ao carregar posts:', error);
          alert(error.message || 'Erro ao carregar os posts.');
          return;
      }

      const result = await response.json();
      console.log('Posts carregados com sucesso:', result.posts);
      // Atualize a interface com os posts carregados
  } catch (err) {
      console.error('Erro de rede:', err);
      alert('Erro ao conectar ao servidor.');
  }
}


// Selecionar todos os posts na página
const postItems = document.querySelectorAll('.post-item');

// Adicionar event listeners a botões de editar e deletar
postItems.forEach(post => {
    // Selecionar o botão Editar
    const editButton = post.querySelector('.dropdown-item.btn.btn-primary');
    if (editButton) {
        editButton.addEventListener('click', (e) => {
            e.preventDefault();
            const editUrl = editButton.getAttribute('href');
            window.location.href = editUrl; // Redireciona para a página de edição
        });
    }

    // Selecionar o botão Deletar
    const deleteForm = post.querySelector('form[action]');
    if (deleteForm) {
        const deleteButton = deleteForm.querySelector('button');
        deleteButton.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this post?')) {
                e.preventDefault(); // Cancela o envio do formulário se o usuário não confirmar
            }
        });
    }
});


// Selecionar o formulário de edição
const editForm = document.querySelector('.edit-post-container form');

// Event listener para salvar alterações
if (editForm) {
    editForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Impede o envio do formulário para manipulação com AJAX (se necessário)

        const contentField = editForm.querySelector('#content');
        const isPublicCheckbox = editForm.querySelector('#is_public');

        // Obtém os valores dos campos
        const content = contentField.value;
        const isPublic = isPublicCheckbox.checked ? 1 : 0;

        // Enviar os dados para o backend (exemplo com fetch)
        const postId = editForm.getAttribute('action').split('/').pop(); // Extrai o ID do post da URL
        updatePost(postId, { content, is_public: isPublic }); // Chama a função definida anteriormente no app.js
    });
}
*/