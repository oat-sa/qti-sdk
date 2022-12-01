<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\ToleranceMode;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ToleranceModeTest
 */
class ToleranceModeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return ToleranceMode::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'exact',
            'absolute',
            'relative',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'EXACT',
            'ABSOLUTE',
            'RELATIVE',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            ToleranceMode::EXACT,
            ToleranceMode::ABSOLUTE,
            ToleranceMode::RELATIVE,
        ];
    }
}
