<?php

namespace qtismtest\data;

use qtism\data\NavigationMode;
use qtismtest\QtiSmEnumTestCase;

class NavigationModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return NavigationMode::class;
    }

    protected function getNames()
    {
        return [
            'linear',
            'nonlinear',
        ];
    }

    protected function getKeys()
    {
        return [
            'LINEAR',
            'NONLINEAR',
        ];
    }

    protected function getConstants()
    {
        return [
            NavigationMode::LINEAR,
            NavigationMode::NONLINEAR,
        ];
    }
}
