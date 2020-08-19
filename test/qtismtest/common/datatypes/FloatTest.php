<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiFloat;
use qtismtest\QtiSmTestCase;

class FloatTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $float = new QtiFloat(null);
    }
}
