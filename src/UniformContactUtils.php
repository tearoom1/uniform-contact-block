<?php

namespace TearoomOne\UniformContactBlock;

class UniformContactUtils
{
    static function printAsset($page, $assetType): void
    {
        if (!option('tearoom1.uniform-contact-block.enabled', true)) {
            return;
        }
        if (option('tearoom1.uniform-contact-block.alwaysIncludeAssets', true)) {
            self::printAssetString($assetType);
            return;
        }
        // otherwise check if there is a uniform contact block present on the page
        $fieldsToCheck = array_keys(array_filter($page->blueprint()->fields(),
            fn($item) => in_array($item['type'], ['blocks', 'layout'])));
        foreach ($fieldsToCheck as $fieldName) {
            if ($page->{$fieldName}()->toBlocks()->hasType('uniform-contact')) {
                self::printAssetString($assetType);
                break;
            }
        }
    }

    /**
     * @param $assetType
     * @return void
     */
    private static function printAssetString($assetType): void
    {
        if ($assetType === 'css') {
            echo css(['media/plugins/tearoom1/uniform-contact-block/css/uniform-contact.css']);
        } elseif ($assetType === 'js') {
            echo js(['media/plugins/tearoom1/uniform-contact-block/js/uniform-contact.js']);
        }
    }
}
