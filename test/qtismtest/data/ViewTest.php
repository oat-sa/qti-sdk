<?php

namespace qtismtest\data;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\View;

class ViewTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return View::class;
    }
    
    protected function getNames()
    {
        return array(
            'author',
            'candidate',
            'proctor',
            'scorer',
            'testConstructor',
            'tutor'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'AUTHOR',
            'CANDIDATE',
            'PROCTOR',
            'SCORER',
            'TEST_CONSTRUCTOR',
            'TUTOR'
        );
    }
    
    protected function getConstants()
    {
        return array(
            View::AUTHOR,
            View::CANDIDATE,
            View::PROCTOR,
            View::SCORER,
            View::TEST_CONSTRUCTOR,
            View::TUTOR
        );
    }
}
