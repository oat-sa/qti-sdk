<?php

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanParameter;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class BeanParameterTest
 */
class BeanParameterTest extends QtiSmTestCase
{
    public function testNoParameter()
    {
        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("No such parameter 'method' for method 'getMethod' of class 'stdClass'.");
        $this->expectExceptionCode(BeanException::NO_PARAMETER);

        $beanParam = new BeanParameter(stdClass::class, 'getMethod', 'method');
    }
}
