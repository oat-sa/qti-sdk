<?php
namespace qtismtest\common\beans;

use qtismtest\QtiSmTestCase;
use qtism\common\beans\BeanMethod;
use qtism\common\beans\BeanException;
use \ReflectionClass;

class BeanMethodTest extends QtiSmTestCase {
	
    public function testNoMethod() {
        $class = new ReflectionClass('qtismtest\\common\\beans\\mocks\\SimpleBean');
        $this->setExpectedException('qtism\\common\\beans\\BeanException', "The method 'unknownMethod' does not exist.", BeanException::NO_METHOD);
        $beanMethod = new BeanMethod($class, 'unknownMethod');
    }
}