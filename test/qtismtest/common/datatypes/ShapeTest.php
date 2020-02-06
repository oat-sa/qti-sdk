<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiShape;
use qtismtest\QtiSmEnumTestCase;

class ShapeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return QtiShape::class;
    }

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
