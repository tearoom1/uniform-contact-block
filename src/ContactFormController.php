<?php

namespace TearoomOne\UniformContactBlock;

use Kirby\Exception\Exception;
use Kirby\Toolkit\I18n;
use Uniform\Form;

class ContactFormController
{
    /**
     * Validate the language code from the URL.
     * Returns the code to use, null for single-language sites
     * or false for codes that are no configured language.
     */
    public static function resolveLanguage(?string $lang): string|null|false
    {
        $kirby = kirby();

        if ($kirby->multilang() === false) {
            return $lang === null ? null : false;
        }

        if ($lang === null) {
            return $kirby->defaultLanguage()?->code();
        }

        return $kirby->language($lang) !== null ? $lang : false;
    }

    public static function contactFormSend(?string $lang, bool $ajax = false): array
    {

        if (!option('tearoom1.uniform-contact-block.enabled', true)) {
            return [['message' => 'This plugin is disabled'], 400];
        }

        $lang = self::resolveLanguage($lang);
        if ($lang === false) {
            return [['message' => 'Unknown language'], 404];
        }

        // tell kirby to use lang
        if ($lang !== null) {
            I18n::$locale = $lang;
        }

        $form = new Form([
            'name' => [
                'rules' => r(option('tearoom1.uniform-contact-block.formNameRequired', false), ['required']),
                'message' => t('tearoom1.uniform-contact-block.name.required'),
            ],
            'email' => [
                'rules' => r(option('tearoom1.uniform-contact-block.formEmailRequired', false), ['required', 'email']),
                'message' => t('tearoom1.uniform-contact-block.email.required'),
            ],
            'message' => [
                'rules' => r(option('tearoom1.uniform-contact-block.formMessageRequired', false), ['required']),
                'message' => t('tearoom1.uniform-contact-block.message.required'),
            ],
        ]);

        if (option('debug') && $form->data('message') === 'error') {
            throw new Exception('Test Error');
        }

        $bypassBaseChecks = option('debug') && strpos($form->data('message'), 'test') !== false;

        if ($ajax) {
            // Perform validation and execute guards.
            $form->withoutFlashing()
                ->withoutRedirect();
        }

        if (!$bypassBaseChecks) {
            $form->simplecaptchaGuard();
            $form->honeypotGuard();
        }

        $form->spamWordsGuard();

        if (option('uniform.honeytime.key') !== null) {
            $form->honeytimeGuard(['key' => option('uniform.honeytime.key')]);
        }

        if (!$form->success()) {
            // Return validation errors.
            return [$form->errors(), 400];
        }

        // If validation and guards passed, execute the action.
        $name = $form->data('name', '', false);
        $subject = I18n::template('tearoom1.uniform-contact-block.subject', null, [
            'name' => $name
        ]);
        $form = $form->emailAction([
            'to' => option('tearoom1.uniform-contact-block.toEmail'),
            'from' => option('tearoom1.uniform-contact-block.fromEmail'),
            'fromName' => option('tearoom1.uniform-contact-block.fromName') . ' ' . t('tearoom1.uniform-contact-block.title'),
            'replyTo' => $form->data('email'),
            'subject' => $subject,
            'escapeHtml' => option('tearoom1.uniform-contact-block.emailEscapeHtml', false)
        ]);

        // The confirmation goes to the submitted address. It can be disabled
        // so the form can't be used to send mails to arbitrary recipients.
        if (option('tearoom1.uniform-contact-block.confirmationEmail', true)) {
            // Whoever fills in the form controls the name, so it must not carry
            // links or long texts to that address (the mail above keeps the original)
            $form->data('name', self::confirmationName($name) ?: ' ');
            $form->emailAction([
                // Send the success email to the email address of the submitter.
                'to' => $form->data('email'),
                'replyTo' => option('tearoom1.uniform-contact-block.fromEmail'),
                'from' => option('tearoom1.uniform-contact-block.fromEmail'),
                'fromName' => option('tearoom1.uniform-contact-block.fromName'),
                'subject' => t('tearoom1.uniform-contact-block.subject_submitter'),
                // Use a template for the email body (see below).
                'template' => self::confirmationTemplate($lang),
                'escapeHtml' => option('tearoom1.uniform-contact-block.emailEscapeHtml', false)
            ]);
        }

        if (!$ajax) {
            $form->done();
        }

        if (!$form->success()) {
            // This should not happen and is our fault.
            return [$form->errors(), 500];
        }

        return [['message' => [t('tearoom1.uniform-contact-block.successMessage')]], 200];
    }

    /**
     * The submitted name as it may appear in the confirmation: without markup,
     * links and email addresses, at most 50 characters
     */
    public static function confirmationName(string $name): string
    {
        $name = strip_tags($name);
        $name = preg_replace('~\b(?:https?://|www\.)\S*~iu', ' ', $name);
        $name = preg_replace('~\S+@\S+~u', ' ', $name);
        // bare domains like spam.example or spam.example/path
        $name = preg_replace('~\b[\p{L}\p{N}-]+(?:\.[\p{L}\p{N}-]+)*\.[a-z]{2,}\b\S*~iu', ' ', $name);
        $name = trim(preg_replace('~[\s\p{C}]+~u', ' ', $name));

        return mb_strlen($name) > 50 ? rtrim(mb_substr($name, 0, 50)) : $name;
    }

    /**
     * Email template for the confirmation, falling back to English
     * for languages without their own template.
     */
    public static function confirmationTemplate(?string $lang): string
    {
        $template = 'success_response_' . ($lang ?? 'en');

        return kirby()->template('emails/' . $template)->exists()
            ? $template
            : 'success_response_en';
    }
}
