<?php

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiInteger;
use qtismtest\QtiSmTestCase;

/**
 * Class IntegerTest
 *
 * @package qtismtest\common\datatypes
 */
class IntegerTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $integer = new QtiInteger(13.37);
    }
}
