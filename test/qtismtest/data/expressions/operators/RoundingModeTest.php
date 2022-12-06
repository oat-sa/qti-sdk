<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\RoundingMode;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class RoundingModeTest
 */
class RoundingModeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return RoundingMode::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'significantFigures',
            'decimalPlaces',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'SIGNIFICANT_FIGURES',
            'DECIMAL_PLACES',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            RoundingMode::SIGNIFICANT_FIGURES,
            RoundingMode::DECIMAL_PLACES,
        ];
    }
}
