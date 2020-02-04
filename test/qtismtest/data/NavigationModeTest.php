<?php

namespace qtismtest\data;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\NavigationMode;

class NavigationModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return NavigationMode::class;
    }
    
    protected function getNames()
    {
        return array(
            'linear',
            'nonlinear'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'LINEAR',
            'NONLINEAR'
        );
    }
    
    protected function getConstants()
    {
        return array(
            NavigationMode::LINEAR,
            NavigationMode::NONLINEAR
        );
    }
}
