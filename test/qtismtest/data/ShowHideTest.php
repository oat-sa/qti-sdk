<?php

namespace qtismtest\data;

use qtism\data\ShowHide;
use qtismtest\QtiSmEnumTestCase;

class ShowHideTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return ShowHide::class;
    }

    protected function getNames()
    {
        return [
            'show',
            'hide',
        ];
    }

    protected function getKeys()
    {
        return [
            'SHOW',
            'HIDE',
        ];
    }

    protected function getConstants()
    {
        return [
            ShowHide::SHOW,
            ShowHide::HIDE,
        ];
    }
}
