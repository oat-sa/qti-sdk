<?php

namespace qtismtest\data\content\interactions;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\content\interactions\Orientation;

class OrientationTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Orientation::class;
    }
    
    protected function getNames()
    {
        return array(
            'vertical',
            'horizontal'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'VERTICAL',
            'HORIZONTAL'
        );
    }
    
    protected function getConstants()
    {
        return array(
            Orientation::VERTICAL,
            Orientation::HORIZONTAL
        );
    }
}
