<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\Orientation;
use qtismtest\QtiSmEnumTestCase;

class OrientationTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Orientation::class;
    }

    protected function getNames()
    {
        return [
            'vertical',
            'horizontal',
        ];
    }

    protected function getKeys()
    {
        return [
            'VERTICAL',
            'HORIZONTAL',
        ];
    }

    protected function getConstants()
    {
        return [
            Orientation::VERTICAL,
            Orientation::HORIZONTAL,
        ];
    }
}
