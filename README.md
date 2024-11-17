# Contact Form Block Plugin for Kirby Uniform

This plugin implements a contact form block using Martin Zurowietz' [`kirby-uniform`](https://github.com/mzur/kirby-uniform) plugin for Kirby.

## Getting started

Use one of the following methods to install & use `tearoom1/uniform-contact-block`:


### Git submodule

If you know your way around Git, you can download this plugin as a [submodule](https://github.com/blog/2104-working-with-submodules):

```text
git submodule add https://github.com/tearoom1/uniform-contact-block.git site/plugins/uniform-contact-block
```


### Composer

```text
composer require tearoom1/uniform-contact-block
```


### Clone or download

1. Clone or download this repository from github: https://github.com/tearoom1/uniform-contact-block.git
2. Unzip / Move the folder to `site/plugins`.


## Usage

Use the block by adding it to you blueprints fieldsets if they are defined:

```yaml
fieldsets:
  - uniform-contact
```

### Configuration

You may change certain options from your `config.php` globally:

```php
return [
    'tearoom1.uniform-contact-block' => [
        'fromEmail' => 'mail@example.org',
        'toEmail' => 'mail@example.org',
        'fromName' => 'My Name',
    ],
];
```


## License

This plugin is licensed under the [MIT License](LICENSE), but **using Kirby in production** requires you to [buy a license](https://getkirby.com/buy).
