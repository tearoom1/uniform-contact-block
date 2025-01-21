<?php
/** @var \Kirby\Cms\Block $block
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\App $kirby
 */
$lang = $kirby->currentLanguage()->code();
$form = new \Uniform\Form();
?>

<?= css(['media/plugins/tearoom1/uniform-contact-block/css/uniform-contact.css']) ?>

<div class="uniform-contact">
    <form id="uniform-contact__form-<?= $block->id() ?>" class="uniform-contact__form"
          action="/<?= $lang ?>/uniform-contact" method="POST">
        <input type="hidden" name="origin" value="<?= $page->url() ?>">
        <input class="uniform-contact__input uniform-contact__name" name="name" type="text" value="<?= $form->old('name'); ?>"
               placeholder="<?= $block->nameLabel() ?>">
        <input class="uniform-contact__input uniform-contact__email" name="email" type="email"
               value="<?= $form->old('email'); ?>" placeholder="<?= $block->emailLabel() ?>">
        <textarea class="uniform-contact__input uniform-contact__message" name="message"
                  rows="4" placeholder="<?= $block->messageLabel() ?>"><?= $form->old('message'); ?></textarea>
        <?= csrf_field(); ?>
        <?= honeypot_field(); ?>
        <?= honeytime_field(option('uniform.honeytime.key')); ?>
        <div class="uniform-contact__captcha">
            <div class="uniform-contact__captcha-wrapper">
                <div class="uniform-contact__captcha-image">
                    <?= simpleCaptcha(['class' => 'uniform-contact__captcha-img', 'title' => 'solve me!']) ?>
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
            <div class="">
                <div class="uniform-contact__captcha-label"><?= $block->captchaLabel() ?></div>
                <?= simpleCaptchaField(null, ['class' => 'uniform-contact__captcha-input']) ?>
            </div>
        </div>
        <?php
        // add link to privacy policy

        ?>
        <div class="uniform-contact__privacy">
            <?= $block->privacyNote() ?>
        </div>
        <input class="uniform-contact__submit-btn" type="submit" value="<?= $block->submitLabel() ?>">
        <div class="uniform-contact__result">
            <div class="uniform-contact__js-message">
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

<?= js(['media/plugins/tearoom1/uniform-contact-block/js/uniform-contact.js']) ?>
