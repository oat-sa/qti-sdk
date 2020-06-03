<?php

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanProperty;
use qtismtest\QtiSmTestCase;

class BeanPropertyTest extends QtiSmTestCase
{
    public function testNoProperty()
    {
        $this->setExpectedException(
            'qtism\\common\\beans\\BeanException',
            "The class property with name 'prop' does not exist in class '\\stdClass'.",
            BeanException::NO_PROPERTY
        );

        $beanProperty = new BeanProperty('\\stdClass', 'prop');
    }

    public function testPropertyNotAnnotated()
    {
        $this->setExpectedException(
            'qtism\\common\\beans\\BeanException',
            "The property with name 'anotherUselessProperty' for class 'qtismtest\\common\\beans\\mocks\\SimpleBean' is not annotated.",
            BeanException::NO_PROPERTY
        );

        $beanProperty = new BeanProperty('qtismtest\\common\\beans\\mocks\\SimpleBean', 'anotherUselessProperty');
    }
}
