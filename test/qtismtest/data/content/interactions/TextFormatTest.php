<?php

namespace qtismtest\data\content\interactions;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\content\interactions\TextFormat;

class TextFormatTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TextFormat::class;
    }
    
    protected function getNames()
    {
        return array(
            'plain',
            'preFormatted',
            'xhtml'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'PLAIN',
            'PRE_FORMATTED',
            'XHTML'
        );
    }
    
    protected function getConstants()
    {
        return array(
            TextFormat::PLAIN,
            TextFormat::PRE_FORMATTED,
            TextFormat::XHTML
        );
    }
}
