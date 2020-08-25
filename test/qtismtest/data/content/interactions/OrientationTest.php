<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\Orientation;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class OrientationTest
 *
 * @package qtismtest\data\content\interactions
 */
class OrientationTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return Orientation::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'vertical',
            'horizontal',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'VERTICAL',
            'HORIZONTAL',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            Orientation::VERTICAL,
            Orientation::HORIZONTAL,
        ];
    }
}
