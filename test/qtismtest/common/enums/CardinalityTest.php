<?php

namespace qtismtest\common\enums;

use qtism\common\enums\Cardinality;
use qtismtest\QtiSmEnumTestCase;

class CardinalityTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Cardinality::class;
    }

    protected function getNames()
    {
        return [
            'single',
            'multiple',
            'ordered',
            'record',
        ];
    }

    protected function getKeys()
    {
        return [
            'SINGLE',
            'MULTIPLE',
            'ORDERED',
            'RECORD',
        ];
    }

    protected function getConstants()
    {
        return [
            Cardinality::SINGLE,
            Cardinality::MULTIPLE,
            Cardinality::ORDERED,
            Cardinality::RECORD,
        ];
    }
}
