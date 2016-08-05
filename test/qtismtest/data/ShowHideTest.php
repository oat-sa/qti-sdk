<?php
namespace qtismtest\data;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\ShowHide;

class ShowHideTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return ShowHide::class;
    }
    
    protected function getNames()
    {
        return array(
            'show',
            'hide'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'SHOW',
            'HIDE'
        );
    }
    
    protected function getConstants()
    {
        return array(
            ShowHide::SHOW,
            ShowHide::HIDE
        );
    }
}
