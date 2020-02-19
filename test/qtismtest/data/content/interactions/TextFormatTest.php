<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\TextFormat;
use qtismtest\QtiSmEnumTestCase;

class TextFormatTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TextFormat::class;
    }

    protected function getNames()
    {
        return [
            'plain',
            'preFormatted',
            'xhtml',
        ];
    }

    protected function getKeys()
    {
        return [
            'PLAIN',
            'PRE_FORMATTED',
            'XHTML',
        ];
    }

    protected function getConstants()
    {
        return [
            TextFormat::PLAIN,
            TextFormat::PRE_FORMATTED,
            TextFormat::XHTML,
        ];
    }
}
