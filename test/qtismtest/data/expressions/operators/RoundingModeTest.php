<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\RoundingMode;
use qtismtest\QtiSmEnumTestCase;

class RoundingModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return RoundingMode::class;
    }

    protected function getNames()
    {
        return [
            'significantFigures',
            'decimalPlaces',
        ];
    }

    protected function getKeys()
    {
        return [
            'SIGNIFICANT_FIGURES',
            'DECIMAL_PLACES',
        ];
    }

    protected function getConstants()
    {
        return [
            RoundingMode::SIGNIFICANT_FIGURES,
            RoundingMode::DECIMAL_PLACES,
        ];
    }
}
