<?php

namespace qtismtest\data;

use qtism\data\NavigationMode;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class NavigationModeTest
 */
class NavigationModeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return NavigationMode::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'linear',
            'nonlinear',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'LINEAR',
            'NONLINEAR',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            NavigationMode::LINEAR,
            NavigationMode::NONLINEAR,
        ];
    }
}
