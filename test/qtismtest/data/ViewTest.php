<?php

namespace qtismtest\data;

use qtism\data\View;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ViewTest
 */
class ViewTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return View::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
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
    protected function getKeys(): array
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
    protected function getConstants(): array
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
