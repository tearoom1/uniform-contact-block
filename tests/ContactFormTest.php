<?php

namespace TearoomOne\UniformContactBlock\Tests;

use Kirby\Email\Email;
use TearoomOne\UniformContactBlock\ContactFormController;

/**
 * Current behaviour of the form, so later changes keep existing sites working
 */
class ContactFormTest extends TestCase
{
    public function testValidMessageIsSentToTheOwnerAndConfirmed(): void
    {
        [$messages, $code] = $this->submit($this->validMessage());

        $this->assertSame(200, $code, json_encode($messages));
        $this->assertCount(2, Email::$emails);

        [$toOwner, $confirmation] = Email::$emails;
        $this->assertSame(['owner@example.test'], array_keys($toOwner->to()));
        $this->assertSame('jane@example.test', $toOwner->replyTo());
        $this->assertStringContainsString('Jane Doe', $toOwner->subject());
        $this->assertStringContainsString('know more about your services', $toOwner->body()->text());

        $this->assertSame(['jane@example.test'], array_keys($confirmation->to()));
        $this->assertStringContainsString('Jane Doe', $confirmation->body()->text());
    }

    public function testConfirmationCanBeTurnedOff(): void
    {
        [, $code] = $this->submit($this->validMessage(), [
            'tearoom1.uniform-contact-block' => ['confirmationEmail' => false],
        ]);

        $this->assertSame(200, $code);
        $this->assertCount(1, Email::$emails);
        $this->assertSame(['owner@example.test'], array_keys(Email::$emails[0]->to()));
    }

    public function testWrongCaptchaIsRejected(): void
    {
        [$messages, $code] = $this->submit($this->validMessage(), [], 'wrong');

        $this->assertSame(400, $code);
        $this->assertArrayHasKey('simple-captcha', $messages);
        $this->assertSame([], Email::$emails);
    }

    public function testFilledHoneypotIsRejected(): void
    {
        [, $code] = $this->submit(array_merge($this->validMessage(), ['website' => 'https://spam.example']));

        $this->assertSame(400, $code);
        $this->assertSame([], Email::$emails);
    }

    public function testRequiredFieldsAreValidated(): void
    {
        [$messages, $code] = $this->submit(['name' => 'Jane Doe', 'email' => '', 'message' => '']);

        $this->assertSame(400, $code);
        $this->assertArrayHasKey('email', $messages);
        $this->assertArrayHasKey('message', $messages);
        $this->assertSame([], Email::$emails);
    }

    public function testDisabledPluginSendsNothing(): void
    {
        [, $code] = $this->submit($this->validMessage(), [
            'tearoom1.uniform-contact-block' => ['enabled' => false],
        ]);

        $this->assertSame(400, $code);
        $this->assertSame([], Email::$emails);
    }

    public function testLanguageOfASingleLanguageSite(): void
    {
        $this->app();

        $this->assertNull(ContactFormController::resolveLanguage(null));
        $this->assertFalse(ContactFormController::resolveLanguage('de'));
    }

    public function testLanguageOfAMultiLanguageSite(): void
    {
        $this->app([], [
            'languages' => [
                ['code' => 'en', 'default' => true],
                ['code' => 'de'],
            ],
        ]);

        $this->assertSame('en', ContactFormController::resolveLanguage(null));
        $this->assertSame('de', ContactFormController::resolveLanguage('de'));
        $this->assertFalse(ContactFormController::resolveLanguage('fr'));
    }

    public function testConfirmationTemplateFallsBackToEnglish(): void
    {
        $this->app();

        $this->assertSame('success_response_de', ContactFormController::confirmationTemplate('de'));
        $this->assertSame('success_response_en', ContactFormController::confirmationTemplate('fr'));
        $this->assertSame('success_response_en', ContactFormController::confirmationTemplate(null));
    }
}
