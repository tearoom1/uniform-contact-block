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
?>
<div class="uniform-contact__container">
    <form id="uniform-contact__form-<?= $block->id() ?>"
          class="uniform-contact__form uniform-contact__layout--<?= $block->layout() ?>"
          action="/<?= $lang ?>/uniform-contact" method="POST"
    <?=option('tearoom1.uniform-contact-block.formBrowserValidate', 'validate')?>>
        <div class="uniform-contact__js-message-modal uniform-contact__js-hidden">
            <div class="uniform-contact__js-success"><?= t('tearoom1.uniform-contact-block.successMessage') ?></div>
            <button class="uniform-contact__js-close">
                <?= t('tearoom1.uniform-contact-block.anotherMessage') ?>
            </button>
        </div>
        <?= csrf_field(); ?>
        <?= honeypot_field(); ?>
        <?php
        $honeyTimeKey = option('uniform.honeytime.key');
        if ($honeyTimeKey) {
            echo honeytime_field($honeyTimeKey);
        }?>
        <input type="hidden" name="origin" value="<?= $page->url() ?>">
        <div class="uniform-contact__input-group">
            <?php $printLabels = $block->printLabels()->toBool();
            if ($printLabels): ?>
                <label for="name" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->nameLabel() ?></span>
                </label>
            <?php endif ?>
            <input class="uniform-contact__input uniform-contact__name"
                   pattern="<?=option('tearoom1.uniform-contact-block.formNamePattern', '.*')?>"
                   title="<?= $block->nameLabel() ?> must be at least 3 characters"
                   name="name" type="text" value="<?= $form->old('name'); ?>"
                <?=r(option('tearoom1.uniform-contact-block.formNameRequired', false), 'required')?>
                   autocomplete="name"
                   placeholder="<?= $printLabels ? '' : $block->nameLabel() ?>">
        </div>
        <div class="uniform-contact__input-group">
            <?php if ($printLabels): ?>
                <label for="email" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->emailLabel() ?></span>
                </label>
            <?php endif ?>
            <input class="uniform-contact__input uniform-contact__email"
                   pattern="<?=option('tearoom1.uniform-contact-block.formEmailPattern', '.*')?>"
                   title="<?= $block->emailLabel() ?> must be a valid email address"
                   name="email" type="email" required value="<?= $form->old('email'); ?>"
                <?=r(option('tearoom1.uniform-contact-block.formEmailRequired', false), 'required')?>
                   autocomplete="email"
                   placeholder="<?= $printLabels ? '' : $block->emailLabel() ?>">
        </div>
        <div class="uniform-contact__input-group uniform-contact__message">
            <?php if ($printLabels): ?>
                <label for="message" class="uniform-contact__label">
                    <span class="uniform-contact__label-text"><?= $block->messageLabel() ?></span>
                </label>
            <?php endif ?>
            <textarea class="uniform-contact__input" name="message" rows="4"
                <?=r(option('tearoom1.uniform-contact-block.formMessageRequired', false), 'required')?>
                placeholder="<?= $printLabels ? '' : $block->messageLabel() ?>"><?= $form->old('message'); ?></textarea>
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
                <?= simpleCaptchaField(null, [
                    'class' => 'uniform-contact__input uniform-contact__captcha-input',
                    'placeholder' => $printLabels ? '' : $block->captchaLabel(),
                    'pattern' => '[^\s]{5}',
                    'required']) ?>
            </div>
            <?php
            // add link to privacy policy

            ?>
            <div class="uniform-contact__privacy">
                <?= $block->privacyNote() ?>
            </div>
            <button class="uniform-contact__submit-btn" type="submit">
                <?= $block->submitLabel() ?>
            </button>
        </div>
        <div class="uniform-contact__result">
            <div class="uniform-contact__js-error">
            </div>
            <?php if ($form->success()): ?>
                <div class="uniform-contact__result--success">
                    <?= t('tearoom1.uniform-contact-block.successMessage') ?>
                </div>
            <?php elseif (count($form->errors()) > 0): ?>
                <div class="uniform-contact__result--error">
                    <?php snippet('uniform/errors', ['form' => $form]); ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>
