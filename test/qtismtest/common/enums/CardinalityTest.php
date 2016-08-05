<?php
namespace qtismtest\common\enums;

use qtismtest\QtiSmEnumTestCase;
use qtism\common\enums\Cardinality;

class CardinalityTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Cardinality::class;
    }
    
    protected function getNames()
    {
        return array(
            'single',
            'multiple',
            'ordered',
            'record'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'SINGLE',
            'MULTIPLE',
            'ORDERED',
            'RECORD'
        );
    }
    
    protected function getConstants()
    {
        return array(
            Cardinality::SINGLE,
            Cardinality::MULTIPLE,
            Cardinality::ORDERED,
            Cardinality::RECORD
        );
    }
}
