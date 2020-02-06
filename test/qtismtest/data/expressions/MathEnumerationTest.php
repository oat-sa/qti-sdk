<?php

namespace qtismtest\data\expressions;

use qtism\data\expressions\MathEnumeration;
use qtismtest\QtiSmEnumTestCase;

class MathEnumerationTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return MathEnumeration::class;
    }

    protected function getNames()
    {
        return [
            'pi',
            'e',
        ];
    }

    protected function getKeys()
    {
        return [
            'PI',
            'E',
        ];
    }

    protected function getConstants()
    {
        return [
            MathEnumeration::PI,
            MathEnumeration::E,
        ];
    }
}
