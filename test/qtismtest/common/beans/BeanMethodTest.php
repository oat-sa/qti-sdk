<?php

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanMethod;
use qtismtest\QtiSmTestCase;
use ReflectionClass;
use qtismtest\common\beans\mocks\SimpleBean;

class BeanMethodTest extends QtiSmTestCase
{
    public function testNoMethod()
    {
        $class = new ReflectionClass(SimpleBean::class);
        $this->setExpectedException(BeanException::class, "The method 'unknownMethod' does not exist.", BeanException::NO_METHOD);
        $beanMethod = new BeanMethod($class, 'unknownMethod');
    }
}
