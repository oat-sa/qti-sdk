<?php

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanParameter;
use qtismtest\QtiSmTestCase;

class BeanParameterTest extends QtiSmTestCase
{
    public function testNoParameter()
    {
        $this->setExpectedException(
            'qtism\\common\\beans\\BeanException',
            "No such parameter 'method' for method 'getMethod' of class '\\stdClass'.",
            BeanException::NO_PARAMETER
        );

        $beanParam = new BeanParameter('\\stdClass', 'getMethod', 'method');
    }
}
