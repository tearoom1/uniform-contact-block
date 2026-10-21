<?php

namespace TearoomOne\UniformContactBlock\Tests;

use Kirby\Cms\App;
use Kirby\Email\Email;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TearoomOne\UniformContactBlock\ContactFormController;
use Uniform\Guards\SimpleCaptchaGuard;

abstract class TestCase extends BaseTestCase
{
    protected string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/uniform-contact-block-tests/' . uniqid();
        Dir::make($this->root . '/content');
        Email::$debug = true;
        Email::$emails = [];
    }

    protected function tearDown(): void
    {
        // release the session file, the next test starts its own session
        App::instance(null, true)?->session()->destroy();
        Email::$debug = false;
        Email::$emails = [];
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD']);
        // no App::destroy(): it would also drop the plugins, which are only registered once
        Dir::remove($this->root);
    }

    protected function app(array $options = [], array $props = []): App
    {
        return new App(array_replace_recursive([
            'roots' => [
                'index'   => $this->root,
                'content' => $this->root . '/content',
                'media'   => $this->root . '/media',
                'cache'   => $this->root . '/cache',
                // outside the test root: Kirby writes the session after tearDown
                'sessions' => sys_get_temp_dir() . '/uniform-contact-block-tests-sessions',
            ],
            'urls' => ['index' => 'https://example.test'],
            'options' => array_replace_recursive([
                'tearoom1.uniform-contact-block' => [
                    'toEmail' => 'owner@example.test',
                    'fromEmail' => 'website@example.test',
                    'fromName' => 'Example',
                ],
            ], $options),
        ], $props));
    }

    /**
     * Submit the form like the AJAX route does: valid CSRF token and solved captcha
     *
     * @return array{0: array, 1: int} Messages and HTTP status
     */
    protected function submit(array $fields, array $options = [], ?string $captchaAnswer = 'abc12'): array
    {
        $kirby = $this->app($options);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $token = csrf();
        $kirby->session()->set(SimpleCaptchaGuard::FLASH_KEY, 'abc12');

        $_POST = array_merge([
            'csrf_token' => $token,
            SimpleCaptchaGuard::FIELD_NAME => $captchaAnswer,
        ], $fields);

        return ContactFormController::contactFormSend(null, true);
    }

    protected function validMessage(): array
    {
        return [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'message' => 'Hello, I would like to know more about your services.',
            // the honeypot field is part of the form and must stay empty
            'website' => '',
        ];
    }
}
