<?php

namespace qtismtest\common\enums;

use qtism\common\enums\Cardinality;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class CardinalityTest
 *
 * @package qtismtest\common\enums
 */
class CardinalityTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return Cardinality::class;
    }

    /**
     * @return array
     */
    protected function getNames()
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
    protected function getKeys()
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
