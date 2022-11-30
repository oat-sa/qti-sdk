<?php

declare(strict_types=1);

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
    protected function getEnumerationFqcn(): string
    {
        return NavigationMode::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'linear',
            'nonlinear',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'LINEAR',
            'NONLINEAR',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            NavigationMode::LINEAR,
            NavigationMode::NONLINEAR,
        ];
    }
}
