<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\Orientation;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class OrientationTest
 */
class OrientationTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return Orientation::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'vertical',
            'horizontal',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'VERTICAL',
            'HORIZONTAL',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            Orientation::VERTICAL,
            Orientation::HORIZONTAL,
        ];
    }
}
