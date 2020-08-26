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
    protected function getEnumerationFqcn()
    {
        return ToleranceMode::class;
    }

    /**
     * @return array
     */
    protected function getNames()
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
    protected function getKeys()
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
    protected function getConstants()
    {
        return [
            ToleranceMode::EXACT,
            ToleranceMode::ABSOLUTE,
            ToleranceMode::RELATIVE,
        ];
    }
}
