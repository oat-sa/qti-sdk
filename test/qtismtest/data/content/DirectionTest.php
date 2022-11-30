<?php

declare(strict_types=1);

namespace qtismtest\data\content;

use qtism\data\content\Direction;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class DirectionTest
 */
class DirectionTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return Direction::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
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
    protected function getKeys(): array
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
    protected function getConstants(): array
    {
        return [
            Direction::AUTO,
            Direction::LTR,
            Direction::RTL,
        ];
    }
}
