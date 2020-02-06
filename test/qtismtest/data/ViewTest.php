<?php

namespace qtismtest\data;

use qtism\data\View;
use qtismtest\QtiSmEnumTestCase;

class ViewTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return View::class;
    }

    protected function getNames()
    {
        return [
            'author',
            'candidate',
            'proctor',
            'scorer',
            'testConstructor',
            'tutor',
        ];
    }

    protected function getKeys()
    {
        return [
            'AUTHOR',
            'CANDIDATE',
            'PROCTOR',
            'SCORER',
            'TEST_CONSTRUCTOR',
            'TUTOR',
        ];
    }

    protected function getConstants()
    {
        return [
            View::AUTHOR,
            View::CANDIDATE,
            View::PROCTOR,
            View::SCORER,
            View::TEST_CONSTRUCTOR,
            View::TUTOR,
        ];
    }
}
