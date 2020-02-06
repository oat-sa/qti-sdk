<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\Param;
use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmTestCase;

class ParamTest extends QtiSmTestCase
{
    public function testCreateWrongNameType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'name' argument must be a string, 'integer' given."
        );
        $param = new Param(999, 'value', ParamType::DATA);
    }

    public function testCreateWrongValueType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'value' argument must be a string, 'integer' given."
        );
        $param = new Param('name', 999, ParamType::DATA);
    }

    public function testCreateNotParamType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'valueType' argument must be a value from the ParamType enumeration, 'boolean' given."
        );
        $param = new Param('name', 'value', true);
    }

    public function testCreateWrongTypeType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'type' argument must be a string, 'integer' given."
        );
        $param = new Param('name', 'value', ParamType::REF, 999);
    }
}
