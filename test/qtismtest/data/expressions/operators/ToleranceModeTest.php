<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\ToleranceMode;
use qtismtest\QtiSmEnumTestCase;

class ToleranceModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return ToleranceMode::class;
    }

    protected function getNames()
    {
        return [
            'exact',
            'absolute',
            'relative',
        ];
    }

    protected function getKeys()
    {
        return [
            'EXACT',
            'ABSOLUTE',
            'RELATIVE',
        ];
    }

    protected function getConstants()
    {
        return [
            ToleranceMode::EXACT,
            ToleranceMode::ABSOLUTE,
            ToleranceMode::RELATIVE,
        ];
    }
}
