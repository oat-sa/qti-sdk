<?php

namespace qtismtest\data\expressions;

use qtism\data\expressions\MathEnumeration;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class MathEnumerationTest
 *
 * @package qtismtest\data\expressions
 */
class MathEnumerationTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return MathEnumeration::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'pi',
            'e',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'PI',
            'E',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            MathEnumeration::PI,
            MathEnumeration::E,
        ];
    }
}
