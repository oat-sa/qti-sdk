<?php

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiFloat;
use qtismtest\QtiSmTestCase;

class FloatTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $float = new QtiFloat(null);
    }
}
