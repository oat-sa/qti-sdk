<?php

namespace qtismtest\data\expressions;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\expressions\MathEnumeration;

class MathEnumerationTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return MathEnumeration::class;
    }
    
    protected function getNames()
    {
        return array(
            'pi',
            'e'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'PI',
            'E'
        );
    }
    
    protected function getConstants()
    {
        return array(
            MathEnumeration::PI,
            MathEnumeration::E
        );
    }
}
