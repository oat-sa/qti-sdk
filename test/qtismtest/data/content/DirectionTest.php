<?php

namespace qtismtest\data\content;

use qtism\data\content\Direction;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class DirectionTest
 *
 * @package qtismtest\data\content
 */
class DirectionTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return Direction::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'auto',
            'ltr',
            'rtl',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'AUTO',
            'LTR',
            'RTL',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            Direction::AUTO,
            Direction::LTR,
            Direction::RTL,
        ];
    }
}
