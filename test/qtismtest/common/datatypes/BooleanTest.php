<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiBoolean;
use qtismtest\QtiSmTestCase;

class BooleanTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $boolean = new QtiBoolean('string');
    }

    public function testClone()
    {
        $boolean = new QtiBoolean(true);
        $otherBoolean = clone $boolean;

        $this->assertEquals($boolean->getValue(), $otherBoolean->getValue());
        $this->assertNotSame($boolean, $otherBoolean);

        $otherBoolean->setValue(false);
        $this->assertNotEquals($boolean->getValue(), $otherBoolean->getValue());
    }
}
