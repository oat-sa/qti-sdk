<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\TextFormat;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class TextFormatTest
 */
class TextFormatTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return TextFormat::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'plain',
            'preFormatted',
            'xhtml',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'PLAIN',
            'PRE_FORMATTED',
            'XHTML',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            TextFormat::PLAIN,
            TextFormat::PRE_FORMATTED,
            TextFormat::XHTML,
        ];
    }
}
