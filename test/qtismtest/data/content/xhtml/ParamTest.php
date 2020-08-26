<?php

namespace qtismtest\data\content\xhtml;

use InvalidArgumentException;
use qtism\data\content\xhtml\Param;
use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmTestCase;

/**
 * Class ParamTest
 */
class ParamTest extends QtiSmTestCase
{
    public function testCreateWrongNameType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'name' argument must be a string, 'integer' given.");
        $param = new Param(999, 'value', ParamType::DATA);
    }

    public function testCreateWrongValueType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'value' argument must be a string, 'integer' given.");
        $param = new Param('name', 999, ParamType::DATA);
    }

    public function testCreateNotParamType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'valueType' argument must be a value from the ParamType enumeration, 'boolean' given.");
        $param = new Param('name', 'value', true);
    }

    public function testCreateWrongTypeType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'type' argument must be a string, 'integer' given.");
        $param = new Param('name', 'value', ParamType::REF, 999);
    }
}
