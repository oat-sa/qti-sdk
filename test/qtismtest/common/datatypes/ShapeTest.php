<?php

namespace qtismtest\common\datatypes;

use qtismtest\QtiSmEnumTestCase;
use qtism\common\datatypes\QtiShape;

class ShapeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return QtiShape::class;
    }
    
    protected function getNames()
    {
        return array(
            'default',
            'rect',
            'circle',
            'poly',
            'ellipse'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'DEF',
            'RECT',
            'CIRCLE',
            'POLY',
            'ELLIPSE'
        );
    }
    
    protected function getConstants()
    {
        return array(
            QtiShape::DEF,
            QtiShape::RECT,
            QtiShape::CIRCLE,
            QtiShape::POLY,
            QtiShape::ELLIPSE
        );
    }
}
