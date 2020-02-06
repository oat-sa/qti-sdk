<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmEnumTestCase;

class ParamTypeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return ParamType::class;
    }

    protected function getNames()
    {
        return [
            'DATA',
            'REF',
        ];
    }

    protected function getKeys()
    {
        return [
            'DATA',
            'REF',
        ];
    }

    protected function getConstants()
    {
        return [
            ParamType::DATA,
            ParamType::REF,
        ];
    }
}
