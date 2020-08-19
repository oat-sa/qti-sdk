<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiInteger;
use qtismtest\QtiSmTestCase;

class IntegerTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $integer = new QtiInteger(13.37);
    }
}
