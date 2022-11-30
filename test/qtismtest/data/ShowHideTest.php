<?php

declare(strict_types=1);

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
    protected function getEnumerationFqcn(): string
    {
        return ShowHide::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'show',
            'hide',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'SHOW',
            'HIDE',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            ShowHide::SHOW,
            ShowHide::HIDE,
        ];
    }
}
