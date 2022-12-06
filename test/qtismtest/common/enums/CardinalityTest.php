<?php

namespace qtismtest\common\enums;

use qtism\common\enums\Cardinality;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class CardinalityTest
 */
class CardinalityTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return Cardinality::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'single',
            'multiple',
            'ordered',
            'record',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'SINGLE',
            'MULTIPLE',
            'ORDERED',
            'RECORD',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            Cardinality::SINGLE,
            Cardinality::MULTIPLE,
            Cardinality::ORDERED,
            Cardinality::RECORD,
        ];
    }
}
