<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ParamTypeTest
 */
class ParamTypeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return ParamType::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'DATA',
            'REF',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'DATA',
            'REF',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            ParamType::DATA,
            ParamType::REF,
        ];
    }
}
