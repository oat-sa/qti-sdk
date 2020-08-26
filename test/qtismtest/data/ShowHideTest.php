<?php

namespace qtismtest\data;

use qtism\data\ShowHide;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ShowHideTest
 */
class ShowHideTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return ShowHide::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'show',
            'hide',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'SHOW',
            'HIDE',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            ShowHide::SHOW,
            ShowHide::HIDE,
        ];
    }
}
