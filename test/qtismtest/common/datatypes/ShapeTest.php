<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiShape;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ShapeTest
 */
class ShapeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return QtiShape::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
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
    protected function getKeys(): array
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
    protected function getConstants(): array
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
