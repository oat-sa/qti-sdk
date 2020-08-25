<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiShape;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ShapeTest
 *
 * @package qtismtest\common\datatypes
 */
class ShapeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return QtiShape::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'default',
            'rect',
            'circle',
            'poly',
            'ellipse',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'DEF',
            'RECT',
            'CIRCLE',
            'POLY',
            'ELLIPSE',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            QtiShape::DEF,
            QtiShape::RECT,
            QtiShape::CIRCLE,
            QtiShape::POLY,
            QtiShape::ELLIPSE,
        ];
    }
}
