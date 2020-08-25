<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\RoundingMode;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class RoundingModeTest
 *
 * @package qtismtest\data\expressions\operators
 */
class RoundingModeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return RoundingMode::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'significantFigures',
            'decimalPlaces',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'SIGNIFICANT_FIGURES',
            'DECIMAL_PLACES',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            RoundingMode::SIGNIFICANT_FIGURES,
            RoundingMode::DECIMAL_PLACES,
        ];
    }
}
