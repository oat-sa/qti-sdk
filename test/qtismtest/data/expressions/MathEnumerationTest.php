<?php

declare(strict_types=1);

namespace qtismtest\data\expressions;

use qtism\data\expressions\MathEnumeration;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class MathEnumerationTest
 */
class MathEnumerationTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return MathEnumeration::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'pi',
            'e',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'PI',
            'E',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            MathEnumeration::PI,
            MathEnumeration::E,
        ];
    }
}
