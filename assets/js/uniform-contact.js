function flashMessage(div) {
  div.style.filter = `brightness(1.1)`;
  setTimeout(function () {
    div.style.filter = `brightness(1)`;
  }, 200);
}

function contactForm(form) {
  var message = form.querySelector('.uniform-contact__js-message');
  var fields = {};
  form.querySelectorAll('[name]').forEach(function (field) {
    fields[field.name] = field;
  });

  var reloadButton = form.querySelector('.uniform-contact__captcha-reload');
  reloadButton.addEventListener('click', function (e) {
    e.preventDefault();
    var captcha = form.querySelector('.uniform-contact__captcha-image');
    fetch('/uniform-contact-captcha').then(function (response) {
      return response.text();
    }).then(function (text) {
      captcha.innerHTML = text;
    });
  });

  // Displays all error messages and adds 'error' classes to the form fields with
  // failed validation.
  var handleError = function (response) {
    var errors = [];
    for (var key in response) {
      if (!response.hasOwnProperty(key)) continue;
      if (fields.hasOwnProperty(key)) fields[key].classList.add('error');
      if (response[key] instanceof Array){
        Array.prototype.push.apply(errors, response[key]);
      } else {
        errors.push(response[key]);
      }
    }
    message.classList.remove('uniform-contact__result--success');
    message.classList.add('uniform-contact__result--error');
    message.innerHTML = errors.join('<br>');
    flashMessage(message);
  }

  var onload = function (e) {
    var response = JSON.parse(e.target.response);
    if (e.target.status === 200) {
      var messages = [];
      for (var key in response) {
        if (!response.hasOwnProperty(key)) continue;
        Array.prototype.push.apply(messages, response[key]);
      }
      message.classList.remove('uniform-contact__result--error');
      message.classList.add('uniform-contact__result--success');
      message.innerHTML = messages.join('<br>');
      flashMessage(message);

      var text = form.querySelector('.uniform-contact__message');
      text.value = '';
      var captchaInput = form.querySelector('.uniform-contact__captcha-input');
      captchaInput.value = '';

      // reload the captcha
      var captcha = form.querySelector('.uniform-contact__captcha-image');

      fetch('/uniform-contact-captcha').then(function (response) {
        return response.text();
      }).then(function (text) {
        captcha.innerHTML = text;
      });
    } else {
      handleError(response);
    }
  };

  var submit = function (e) {
    e.preventDefault();
    var request = new XMLHttpRequest();
    request.open('POST', e.target.action + '-ajax');
    request.onload = onload;
    request.send(new FormData(e.target));
    // Remove all 'error' classes of a possible previously failed validation.
    for (var key in fields) {
      if (!fields.hasOwnProperty(key)) continue;
      fields[key].classList.remove('error');
    }
  };
  form.addEventListener('submit', submit);
}

window.addEventListener('load', function () {
  var forms = document.querySelectorAll('.uniform-contact__form');
  // for each form
  forms.forEach(function (form) {
    contactForm(form);
  });
});
