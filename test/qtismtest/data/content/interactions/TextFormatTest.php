<?php

declare(strict_types=1);

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
    protected function getEnumerationFqcn(): string
    {
        return TextFormat::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
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
    protected function getKeys(): array
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
    protected function getConstants(): array
    {
        return [
            TextFormat::PLAIN,
            TextFormat::PRE_FORMATTED,
            TextFormat::XHTML,
        ];
    }
}
