<?php

namespace qtismtest\data;

use qtism\data\View;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ViewTest
 *
 * @package qtismtest\data
 */
class ViewTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return View::class;
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
