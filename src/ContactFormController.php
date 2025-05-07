<?php

namespace TearoomOne\UniformContactBlock;

use Kirby\Toolkit\I18n;
use Uniform\Form;

class ContactFormController
{
    public static function contactFormSend($lang, $ajax = false): array
    {

        // tell kirby to use lang
        I18n::$locale = $lang;

        $form = new Form([
            'name' => [
                'rules' => ['required'],
                'message' => t('tearoom1.uniform-contact-block.name.required'),
            ],
            'email' => [
                'rules' => ['required', 'email'],
                'message' => t('tearoom1.uniform-contact-block.email.required'),
            ],
            'message' => [
                'rules' => ['required'],
                'message' => t('tearoom1.uniform-contact-block.message.required'),
            ],
        ]);

        if ($ajax) {
            // Perform validation and execute guards.
            $form->withoutFlashing()
                ->withoutRedirect();
        }

        if (!option('debug') || $form->data('message') !== 'test') {
            $form
                ->simplecaptchaGuard()
                ->honeypotGuard()
                ->honeytimeGuard([
                    'key' => option('uniform.honeytime.key'),
                ])
                ->spamWordsGuard();
        } else {
            $form->honeypotGuard();
        }

        if (!$form->success()) {
            // Return validation errors.
            return [$form->errors(), 400];
        }

        // If validation and guards passed, execute the action.
        $form->emailAction([
            'to' => option('tearoom1.uniform-contact-block.toEmail'),
            'from' => option('tearoom1.uniform-contact-block.fromEmail'),
            'fromName' => option('tearoom1.uniform-contact-block.fromName') . ' ' . t('tearoom1.uniform-contact-block.title'),
            'replyTo' => $form->data('email'),
            'subject' => t('tearoom1.uniform-contact-block.subject', $form->data('name')),
        ])
            ->emailAction([
                // Send the success email to the email address of the submitter.
                'to' => $form->data('email'),
                'replyTo' => option('tearoom1.uniform-contact-block.fromEmail'),
                'from' => option('tearoom1.uniform-contact-block.fromEmail'),
                'fromName' => option('tearoom1.uniform-contact-block.fromName'),
                'subject' => t('tearoom1.uniform-contact-block.subject_submitter'),
                // Use a template for the email body (see below).
                'template' => 'success_response_' . $lang,
            ]);

        if (!$ajax) {
            $form->done();
        }

        if (!$form->success()) {
            // This should not happen and is our fault.
            return [$form->errors(), 500];
        }

        return [['message' => [t('tearoom1.uniform-contact-block.successMessage')]], 200];
    }
}
