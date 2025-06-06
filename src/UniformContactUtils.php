<?php

namespace TearoomOne\UniformContactBlock;

class UniformContactUtils
{
    static function printAsset($page, $assetTyoe)
    {
        $bpFields = $page->blueprint()->fields();
        foreach ($page->content()->fields() as $field) {
            if (isset($bpFields[$field->key()])
                && ($bpFields[$field->key()]['type'] === 'blocks'
                    || $bpFields[$field->key()]['type'] === 'layout'
                    || $bpFields[$field->key()]['type'] === 'object')
                && $field->toBlocks()->hasType('uniform-contact')) {
                if ($assetTyoe === 'css') {
                    echo css(['media/plugins/tearoom1/uniform-contact-block/css/uniform-contact.css']);
                } elseif ($assetTyoe === 'js') {
                    echo js(['media/plugins/tearoom1/uniform-contact-block/js/uniform-contact.js']);
                }
                break;
            }
        }
    }
}
