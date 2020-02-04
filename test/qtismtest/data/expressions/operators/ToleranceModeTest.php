<?php

namespace qtismtest\data\expressions\operators;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\expressions\operators\ToleranceMode;

class ToleranceModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return ToleranceMode::class;
    }
    
    protected function getNames()
    {
        return array(
            'exact',
            'absolute',
            'relative'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'EXACT',
            'ABSOLUTE',
            'RELATIVE'
        );
    }
    
    protected function getConstants()
    {
        return array(
            ToleranceMode::EXACT,
            ToleranceMode::ABSOLUTE,
            ToleranceMode::RELATIVE
        );
    }
}
