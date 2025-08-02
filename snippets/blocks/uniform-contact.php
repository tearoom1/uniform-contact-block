<?php
/** @var \Kirby\Cms\Block $block
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\App $kirby
 */
if (!option('tearoom1.uniform-contact-block.enabled', true)) {
    return;
}
$lang = $kirby->currentLanguage()->code();
$form = new \Uniform\Form();
$showForm = !$form->success();
?>
<div class="uniform-contact uniform-contact__theme--<?= option('tearoom1.uniform-contact-block.theme', 'bare') ?>">

    <div class="uniform-contact__confirmation" aria-hidden="<?= $showForm ? 'true' : 'false' ?>">
        <div class="uniform-contact__confirmation-message">
            <?= t('tearoom1.uniform-contact-block.successMessage') ?>
        </div>
        <form action="" method="GET">
            <button>
                <?= t('tearoom1.uniform-contact-block.anotherMessage') ?>
            </button>
        </form>
    </div>

    <form id="uniform-contact__form-<?= $block->id() ?>"
          class="uniform-contact__form uniform-contact__layout--<?= $block->layout() ?>"
          action="/<?= $lang ?>/uniform-contact" method="POST"
          aria-hidden="<?= $showForm ? 'false' : 'true' ?>"
        <?= option('tearoom1.uniform-contact-block.formBrowserValidate', 'validate') ?>>
        <?= csrf_field(); ?>
        <?= honeypot_field(); ?>
        <?php
        $honeyTimeKey = option('uniform.honeytime.key');
        if ($honeyTimeKey) {
            echo honeytime_field($honeyTimeKey);
        } ?>
        <input type="hidden" name="origin" value="<?= $page->url() ?>">
        <div class="uniform-contact__input-group">
            <?php $printLabels = $block->printLabels()->toBool();
            if ($printLabels): ?>
                <label for="name" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->nameLabel() ?></span>
                </label>
            <?php endif ?>
            <input
                class="uniform-contact__input uniform-contact__name <?= (array_key_exists('name', $form->errors())) ? 'error' : '' ?>"
                pattern="<?= option('tearoom1.uniform-contact-block.formNamePattern', '.*') ?>"
                title="<?= $block->nameLabel() ?> must be at least 3 characters"
                name="name" type="text" value="<?= $form->old('name'); ?>"
                <?= r(option('tearoom1.uniform-contact-block.formNameRequired', false), 'required') ?>
                autocomplete="name"
                placeholder="<?= $printLabels ? '' : $block->nameLabel() ?>">
            <?php if (array_key_exists('name', $form->errors())): ?>
                <div class="uniform-contact__error-message">
                    <?= implode('<br>', $form->errors()['name']) ?>
                </div>
            <?php endif ?>
        </div>
        <div class="uniform-contact__input-group">
            <?php if ($printLabels): ?>
                <label for="email" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->emailLabel() ?></span>
                </label>
            <?php endif ?>
            <input
                class="uniform-contact__input uniform-contact__email <?= (array_key_exists('email', $form->errors())) ? 'error' : '' ?>"
                pattern="<?= option('tearoom1.uniform-contact-block.formEmailPattern', '.*') ?>"
                title="<?= $block->emailLabel() ?> must be a valid email address"
                name="email" type="email" required value="<?= $form->old('email'); ?>"
                <?= r(option('tearoom1.uniform-contact-block.formEmailRequired', false), 'required') ?>
                autocomplete="email"
                placeholder="<?= $printLabels ? '' : $block->emailLabel() ?>">
            <?php if (array_key_exists('email', $form->errors())): ?>
                <div class="uniform-contact__error-message">
                    <?= implode('<br>', $form->errors()['email']) ?>
                </div>
            <?php endif ?>
        </div>
        <div class="uniform-contact__input-group uniform-contact__message">
            <?php if ($printLabels): ?>
                <label for="message" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->messageLabel() ?></span>
                </label>
            <?php endif ?>
            <textarea
                class="uniform-contact__input <?= (array_key_exists('message', $form->errors())) ? 'error' : '' ?>"
                name="message" rows="4"
                    <?= r(option('tearoom1.uniform-contact-block.formMessageRequired', false), 'required') ?>
                    placeholder="<?= $printLabels ? '' : $block->messageLabel() ?>"><?= $form->old('message'); ?></textarea>
            <?php if (array_key_exists('message', $form->errors())): ?>
                <div class="uniform-contact__error-message">
                    <?= implode('<br>', $form->errors()['message']) ?>
                </div>
            <?php endif ?>
        </div>
        <div class="uniform-contact__last_block">
            <?php if ($printLabels): ?>
                <label for="captcha" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->captchaLabel() ?></span>
                </label>
            <?php endif ?>
            <div class="uniform-contact__captcha">
                <div class="uniform-contact__captcha-wrapper">
                    <div class="uniform-contact__captcha-image">
                        <?= simpleCaptcha([
                            'class' => 'uniform-contact__captcha-img',
                            'title' => 'Solve me!']) ?>
                    </div>
                    <a class="uniform-contact__captcha-reload">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                             class="icon icon--reload"
                             role="img" aria-labelledby="title">
                            <title>Reload</title>
                            <path
                                d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"
                                stroke="#888" stroke-width="2" stroke-linecap="round" fill="#888"/>
                        </svg>
                    </a>
                </div>
                <div class="uniform-contact__captcha-answer">
                    <?= simpleCaptchaField(null, [
                        'class' => 'uniform-contact__input uniform-contact__captcha-input' . ((array_key_exists('captcha', $form->errors())) ? ' error' : ''),
                        'placeholder' => $printLabels ? '' : $block->captchaLabel(),
                        'type' => 'text',
                        'pattern' => '[^\s]{5}',
                        'required']) ?>
                    <?php if (array_key_exists('simple-captcha', $form->errors())): ?>
                        <div class="uniform-contact__error-message uniform-contact__error-message--captcha">
                            <?= implode('<br>', $form->errors()['simple-captcha']) ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
            <?php
            // add link to privacy policy

            ?>
            <?php if ($block->privacyNote()->isNotEmpty()): ?>
                <div class="uniform-contact__privacy">
                    <?= $block->privacyNote() ?>
                </div>
            <?php endif ?>
            <button class="uniform-contact__submit-btn" type="submit">
                <?= $block->submitLabel() ?>
            </button>
        </div>
        <div class="uniform-contact__result">
            <?php
            // Display any general errors not associated with specific fields
            $generalErrors = array_filter($form->errors(), function ($key) {
                return !in_array($key, ['name', 'email', 'message', 'simple-captcha']);
            }, ARRAY_FILTER_USE_KEY);

            if (count($generalErrors) > 0):
                ?>
                <div class="uniform-contact__result--error">
                    <?php foreach ($generalErrors as $error): ?>
                        <div>
                            <?= implode('<br>', $error) ?>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>
