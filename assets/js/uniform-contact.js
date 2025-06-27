function flashMessage(div) {
  div.style.filter = `brightness(1.1)`;
  setTimeout(function () {
    div.style.filter = `brightness(1)`;
  }, 200);
}

function contactForm(form) {
  const successClose = form.querySelector('.uniform-contact__js-close');
  successClose.addEventListener('click', function (e) {
    e.preventDefault();
    form.querySelector('.uniform-contact__js-message-modal').classList.add('uniform-contact__js-hidden');
  });
  const successMessage = form.querySelector('.uniform-contact__js-success');
  const errorMessage = form.querySelector('.uniform-contact__js-error');
  const fields = {};
  form.querySelectorAll('[name]').forEach(function (field) {
    fields[field.name] = field;
  });

  const reloadButton = form.querySelector('.uniform-contact__captcha-reload');
  reloadButton.addEventListener('click', function (e) {
    e.preventDefault();
    const captcha = form.querySelector('.uniform-contact__captcha-image');
    fetch('/uniform-contact-captcha').then(function (response) {
      return response.text();
    }).then(function (text) {
      captcha.innerHTML = text;
    });
  });

  // Displays all error messages and adds 'error' classes to the form fields with
  // failed validation.
  const handleError = function (response) {
    const errors = [];
    for (const key in response) {
      if (!response.hasOwnProperty(key)) continue;
      if (fields.hasOwnProperty(key)) fields[key].classList.add('error');
      if (response[key] instanceof Array){
        Array.prototype.push.apply(errors, response[key]);
      } else {
        errors.push(response[key]);
      }
    }
    successMessage.innerHTML = '';
    successMessage.classList.remove('uniform-contact__result--success');
    errorMessage.classList.add('uniform-contact__result--error');
    errorMessage.innerHTML = errors.join('<br>');
    flashMessage(errorMessage);
  }

  const onload = function (e) {
    const response = JSON.parse(e.target.response);
    if (e.target.status === 200) {
      const messages = [];
      for (const key in response) {
        if (!response.hasOwnProperty(key)) continue;
        Array.prototype.push.apply(messages, response[key]);
      }
      errorMessage.innerHTML = '';
      errorMessage.classList.remove('uniform-contact__result--error');
      successMessage.classList.add('uniform-contact__result--success');
      successMessage.innerHTML = messages.join('<br>');
      flashMessage(successMessage);
      form.querySelector('.uniform-contact__js-message-modal').classList.remove('uniform-contact__js-hidden');

      form.querySelectorAll('.uniform-contact__input').forEach(function (input) {
        input.value = '';
      });
      const captchaInput = form.querySelector('.uniform-contact__captcha-input');
      captchaInput.value = '';

      // reload the captcha
      const captcha = form.querySelector('.uniform-contact__captcha-image');

      fetch('/uniform-contact-captcha').then(function (response) {
        return response.text();
      }).then(function (text) {
        captcha.innerHTML = text;
      });
    } else {
      handleError(response);
    }
    const submitBtn = form.querySelector('.uniform-contact__submit-btn');
    submitBtn.classList.remove('is-loading'); // Remove loading class
  };

  var submit = function (e) {
    e.preventDefault();
    const submitBtn = form.querySelector('.uniform-contact__submit-btn');
    submitBtn.classList.add('is-loading'); // Add loading class
    const request = new XMLHttpRequest();
    request.open('POST', e.target.action + '-ajax');
    request.onload = onload;
    request.send(new FormData(e.target));
    // Remove all 'error' classes of a possible previously failed validation.
    for (const key in fields) {
      if (!fields.hasOwnProperty(key)) continue;
      fields[key].classList.remove('error');
    }
  };
  form.addEventListener('submit', submit);
}

window.addEventListener('load', function () {
  const forms = document.querySelectorAll('.uniform-contact__form');
  // for each form
  forms.forEach(function (form) {
    contactForm(form);
  });
});
