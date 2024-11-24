function addEventListeners() {
    let postCheckers = document.querySelectorAll('.post-item input[type=checkbox]');
    [].forEach.call(postCheckers, function(checker) {
        checker.addEventListener('change', sendPostUpdateRequest);
    });
  
    let postCreators = document.querySelectorAll('button.new-post-button');
    [].forEach.call(postCreators, function(creator) {
        creator.addEventListener('click', function(event) {
            event.preventDefault();
            sendCreatePostRequest(event);
        });
    });
  
    let postDeleters = document.querySelectorAll('.post-item .delete-post-btn');
    [].forEach.call(postDeleters, function(deleter) {
        deleter.addEventListener('click', sendDeletePostRequest);
    });
  }
  
  function encodeForAjax(data) {
    if (data == null) return null;
    return Object.keys(data).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
    }).join('&');
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
    let item = this.closest('.post-item');
    let id = item.getAttribute('data-id');
    let checked = item.querySelector('input[type=checkbox]').checked;
  
    sendAjaxRequest('post', '/api/posts/' + id, { done: checked }, postUpdatedHandler);
  }
  
  function sendDeletePostRequest() {
    let id = this.closest('.post-item').getAttribute('data-id');
  
    sendAjaxRequest('delete', '/api/posts/' + id, null, postDeletedHandler);
  }
  
  function sendCreatePostRequest(event) {
      let form = document.querySelector('.create-post-form');
      let content = form.querySelector('textarea[name=content]').value;
      let isPublic = form.querySelector('select[name=is_public]').value;
  
      let formData = new FormData();
      formData.append('content', content);
      formData.append('is_public', isPublic);
  
      const imageFields = ['image1', 'image2', 'image3'];
      let imageCount = 0;
      imageFields.forEach(function(field, index) {
          let imageInput = form.querySelector(`input[name=${field}]`);
  
          if (imageInput.files.length > 0) {
              if (imageCount < 3) {
                  formData.append(field, imageInput.files[0]);
                  imageCount++;
              }
          }
      });
  
      if (imageCount > 3) {
          alert('Você pode selecionar no máximo 3 imagens.');
          return;
      }
  
      sendAjaxRequest('POST', '/api/posts', formData, postAddedHandler);
      event.preventDefault();
  }
  
  function postUpdatedHandler() {
    if (this.status != 200) {
        console.error('Erro ao atualizar o post');
        return;
    }
  
    let item = JSON.parse(this.responseText);
    let element = document.querySelector('.post-item[data-id="' + item.id + '"]');
    let checkbox = element.querySelector('input[type=checkbox]');
    checkbox.checked = item.done === "true";
  }
  
  function postAddedHandler() {
    if (this.status != 200) {
        window.location = '/';
        return;
    }
  
    let item = JSON.parse(this.responseText);
    let newItem = createPostElement(item);
  
    document.querySelector('.posts-container').prepend(newItem);
  
    document.querySelector('.create-post-form input[name=content]').value = "";
    document.querySelector('.create-post-form input[name=image1]').value = "";
    document.querySelector('.create-post-form input[name=image2]').value = "";
    document.querySelector('.create-post-form input[name=image3]').value = "";
  }
  
  function postDeletedHandler() {
    if (this.status != 200) {
        window.location = '/';
        return;
    }
  
    let item = JSON.parse(this.responseText);
    let element = document.querySelector('.post-item[data-id="' + item.id + '"]');
    element.remove();
  }
  
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
  
    newItem.querySelector('input[type=checkbox]').addEventListener('change', sendPostUpdateRequest);
    newItem.querySelector('.delete-post-btn').addEventListener('click', sendDeletePostRequest);
  
    return newItem;
  }
  
  addEventListeners();
  