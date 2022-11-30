<?php

declare(strict_types=1);

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiFloat;
use qtismtest\QtiSmTestCase;

/**
 * Class FloatTest
 */
class FloatTest extends QtiSmTestCase
{
    public function testWrongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $float = new QtiFloat(null);
    }
}
