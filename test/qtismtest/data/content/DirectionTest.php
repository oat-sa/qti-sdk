<?php

namespace qtismtest\data\content;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\content\Direction;

class DirectionTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Direction::class;
    }
    
    protected function getNames()
    {
        return array(
            'auto',
            'ltr',
            'rtl'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'AUTO',
            'LTR',
            'RTL'
        );
    }
    
    protected function getConstants()
    {
        return array(
            Direction::AUTO,
            Direction::LTR,
            Direction::RTL,
        );
    }
}
