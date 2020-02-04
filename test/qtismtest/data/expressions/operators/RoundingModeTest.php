<?php

namespace qtismtest\data\expressions\operators;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\expressions\operators\RoundingMode;

class RoundingModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return RoundingMode::class;
    }
    
    protected function getNames()
    {
        return array(
            'significantFigures',
            'decimalPlaces'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'SIGNIFICANT_FIGURES',
            'DECIMAL_PLACES'
        );
    }
    
    protected function getConstants()
    {
        return array(
            RoundingMode::SIGNIFICANT_FIGURES,
            RoundingMode::DECIMAL_PLACES
        );
    }
}
