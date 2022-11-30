<?php

declare(strict_types=1);

namespace qtismtest\common\utils;

use qtism\common\utils\Arrays;
use qtismtest\QtiSmTestCase;

/**
 * Class ArraysTest
 */
class ArraysTest extends QtiSmTestCase
{
    /**
     * @dataProvider isAssocValidProvider
     * @param array $array
     */
    public function testIsAssocValid(array $array): void
    {
        $this::assertTrue(Arrays::isAssoc($array));
    }

    /**
     * @dataProvider isAssocInvalidProvider
     * @param array $array
     */
    public function testIsAssocInvalid(array $array): void
    {
        $this::assertFalse(Arrays::isAssoc($array));
    }

    /**
     * @return array
     */
    public function isAssocValidProvider(): array
    {
        return [
            [['test' => 0, 'bli' => 2]],
        ];
    }

    /**
     * @return array
     */
    public function isAssocInvalidProvider(): array
    {
        return [
            [[0, 1]],
        ];
    }
}
