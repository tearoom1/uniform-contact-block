<?php

load([
    'TearoomOne\\UniformContactBlock\\ContactFormController' => 'src/ContactFormController.php',
    'TearoomOne\\UniformContactBlock\\UniformContactUtils' => 'src/UniformContactUtils.php'
], __DIR__);

use TearoomOne\UniformContactBlock\ContactFormController;

Kirby::plugin('tearoom1/uniform-contact-block', [
    'options' => [
        'enabled' => true,
        'alwaysIncludeAssets' => true,
        'fromEmail' => '',
        'toEmail' => '',
        'fromName' => '',
        'formBrowserValidate' => 'validate',
        'formNameRequired' => true,
        'formEmailRequired' => true,
        'formMessageRequired' => true,
        'formNamePattern' => '[^\s]{3,}',
        'formEmailPattern' => '[^\s@]+@[^\s@]+\.[^\s@]+',
        'theme' => '',
    ],
    'blueprints' => [
        'blocks/uniform-contact' => __DIR__ . '/blueprints/blocks/uniform-contact.yml',
    ],
    'snippets' => [
        'blocks/uniform-contact' => __DIR__ . '/snippets/blocks/uniform-contact.php',
        'uniform-contact/css' => __DIR__ . '/snippets/css.php',
        'uniform-contact/js' => __DIR__ . '/snippets/js.php',
    ],
    'templates' => [
        'emails/success_response_en' => __DIR__ . '/templates/emails/success_response_en.php',
        'emails/success_response_de' => __DIR__ . '/templates/emails/success_response_de.php'
    ],
    'translations' => [
        'en' => [
            'tearoom1.uniform-contact-block.title' => 'Contact Form',
            'tearoom1.uniform-contact-block.name.required' => 'Please fill in your name.',
            'tearoom1.uniform-contact-block.email.required' => 'Please fill in your email.',
            'tearoom1.uniform-contact-block.message.required' => 'Please provide a message.',
            'tearoom1.uniform-contact-block.successMessage' => 'Thank you for your message!<br>We will get back to you as soon as possible.',
            'tearoom1.uniform-contact-block.anotherMessage' => 'Send another message',
            'tearoom1.uniform-contact-block.subject' => 'New message from {name}',
            'tearoom1.uniform-contact-block.subject_submitter' => 'Thank you for your message!',
            'form-csrf-expired' => 'This form is not valid anymore,<br>please reload the page and try again.',
        ],
        'de' => [
            'tearoom1.uniform-contact-block.title' => 'Kontakt Formular',
            'tearoom1.uniform-contact-block.name.required' => 'Bitte gib einen Namen an.',
            'tearoom1.uniform-contact-block.email.required' => 'Bitte gib eine E-Mail an.',
            'tearoom1.uniform-contact-block.message.required' => 'Bitte schreib eine Nachricht.',
            'tearoom1.uniform-contact-block.successMessage' => 'Vielen Dank für Deine Nachricht!<br>Wir antworten so schnell wie möglich.',
            'tearoom1.uniform-contact-block.anotherMessage' => 'Eine weitere Nachricht senden',
            'tearoom1.uniform-contact-block.subject' => 'Neue Nachricht von {name}',
            'tearoom1.uniform-contact-block.subject_submitter' => 'Danke für Deine Nachricht!',
            'form-csrf-expired' => 'Dieses Formular ist nicht mehr gültig,<br>bitte lade die Seite neu und versuche es nochmal.',
        ],
    ],
    'routes' => [
        [
            'pattern' => '/uniform-contact-captcha',
            'method' => 'GET',
            'action' => function () {
                // return new captcha image
                return simpleCaptcha();
            }
        ],
        [
            'pattern' => '(:any)/uniform-contact',
            'method' => 'POST',
            'action' => function ($lang) {
                ContactFormController::contactFormSend($lang);
            }
        ],
        [
            'pattern' => '(:any)/uniform-contact-ajax',
            'method' => 'POST',
            'action' => function ($lang) {
                [$messages, $code] = ContactFormController::contactFormSend($lang, true);

                return \Kirby\Cms\Response::json($messages, $code);
            }
        ]
    ]
]);


