<?php

namespace qtismtest\data\content;

use qtism\data\content\Direction;
use qtismtest\QtiSmEnumTestCase;

class DirectionTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Direction::class;
    }

    protected function getNames()
    {
        return [
            'auto',
            'ltr',
            'rtl',
        ];
    }

    protected function getKeys()
    {
        return [
            'AUTO',
            'LTR',
            'RTL',
        ];
    }

    protected function getConstants()
    {
        return [
            Direction::AUTO,
            Direction::LTR,
            Direction::RTL,
        ];
    }
}
