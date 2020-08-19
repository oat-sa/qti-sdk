<?php

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanParameter;
use qtismtest\QtiSmTestCase;
use stdClass;

class BeanParameterTest extends QtiSmTestCase
{
    public function testNoParameter()
    {
        $this->setExpectedException(
            BeanException::class,
            "No such parameter 'method' for method 'getMethod' of class 'stdClass'.",
            BeanException::NO_PARAMETER
        );

        $beanParam = new BeanParameter(stdClass::class, 'getMethod', 'method');
    }
}
