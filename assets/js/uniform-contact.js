function flashMessage(div) {
  div.style.filter = `brightness(1.1)`;
  setTimeout(function () {
    div.style.filter = `brightness(1)`;
  }, 200);
}

function contactForm(block) {
  console.log(block)

  const form = block.querySelector('.uniform-contact__form');
  const confirmationPanel = block.querySelector('.uniform-contact__confirmation');
  const closeButton = confirmationPanel.querySelector('button');
  let resultMessageContainer = form.querySelector('.uniform-contact__result');
  let generalErrorsContainer = resultMessageContainer.querySelector('.uniform-contact__result--error');

  closeButton.addEventListener('click', function (e) {
    e.preventDefault();
    confirmationPanel.setAttribute('aria-hidden', 'true');
    form.setAttribute('aria-hidden', 'false');
  });

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

  // Remove all error messages
  const clearAllErrorMessages = function() {
    resultMessageContainer.setAttribute('aria-hidden', 'true');
    if (generalErrorsContainer) {
      generalErrorsContainer.innerHTML = '';
    }
    form.querySelectorAll('.uniform-contact__error-message').forEach(function(errorDiv) {
      errorDiv.remove();
    });
  };

  // Displays all error messages and adds 'error' classes to the form fields with
  // failed validation.
  const handleError = function (response) {
    console.log(response)
    // Clear any existing error messages first
    clearAllErrorMessages();

    const generalErrors = [];

    for (const key in response) {
      if (!response.hasOwnProperty(key)) continue;

      let responseValue = response[key];

      if (response.hasOwnProperty("exception")) {
        generalErrors.push(responseValue);
        continue;
      }

      if (fields.hasOwnProperty(key)) {
        // Add error class to the input field
        fields[key].classList.add('error');

        // Get the parent block (different handling for captcha)
        let inputGroup = null;
        if (key === 'simple-captcha') {
          // Captcha is inside a different structure
          inputGroup = fields[key].closest('.uniform-contact__captcha-answer');
        } else {
          inputGroup = fields[key].closest('.uniform-contact__input-group');
        }

        if (inputGroup) {
          const errorDiv = document.createElement('div');
          errorDiv.className = 'uniform-contact__error-message';
          if (key === 'simple-captcha') {
            errorDiv.className += ' uniform-contact__error-message--captcha';
          }

          // Add the error message(s)
          if (responseValue instanceof Array) {
            errorDiv.innerHTML = responseValue.join('<br>');
          } else {
            errorDiv.innerHTML = responseValue;
          }

          // Check if error message already exists
          const existingError = inputGroup.querySelector('.uniform-contact__error-message');
          if (existingError) {
            inputGroup.replaceChild(errorDiv, existingError);
          } else {
            inputGroup.appendChild(errorDiv);
          }

          // Focus on the first field with error
          if (document.activeElement !== fields[key]) {
            fields[key].focus();
          }
        }
      } else {
        // Handle general errors (not specific to a field)
        if (responseValue instanceof Array) {
          Array.prototype.push.apply(generalErrors, responseValue);
        } else {
          generalErrors.push(responseValue);
        }
      }
    }

    // Display general errors
    if (generalErrors.length > 0) {
      resultMessageContainer.setAttribute('aria-hidden', 'false');

      // If the block doesn't exist, create it
      if (!generalErrorsContainer) {
        generalErrorsContainer = document.createElement('div');
        generalErrorsContainer.className = 'uniform-contact__result--error';
          resultMessageContainer.appendChild(generalErrorsContainer);
      } else {
        // Clear existing general errors
        generalErrorsContainer.innerHTML = '';
      }

      // Add each error message
      generalErrors.forEach(function(error) {
        const errorDiv = document.createElement('div');
        errorDiv.innerHTML = error;
        generalErrorsContainer.appendChild(errorDiv);
      });
    }

    // Flash the first error message for visibility
    const firstErrorMessage = form.querySelector('.uniform-contact__error-message');
    if (firstErrorMessage) {
      flashMessage(firstErrorMessage);
    }
  }

  const onload = function (e) {
    // Clear all error messages
    clearAllErrorMessages();

    const response = JSON.parse(e.target.response);
    if (e.target.status === 200) {
      const messages = [];
      for (const key in response) {
        if (!response.hasOwnProperty(key)) continue;
        Array.prototype.push.apply(messages, response[key]);
      }
      clearAllErrorMessages();
      form.setAttribute('aria-hidden', 'true');
      setTimeout(
        confirmationPanel.setAttribute('aria-hidden', 'false')
        , 500);
      // closeButton.focus();

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
  const blocks = document.querySelectorAll('.uniform-contact');
  // for each form
  blocks.forEach(function (block) {
    contactForm(block);
  });
});
