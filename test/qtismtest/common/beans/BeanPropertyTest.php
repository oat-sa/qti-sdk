<?php

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanProperty;
use qtismtest\common\beans\mocks\SimpleBean;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class BeanPropertyTest
 *
 * @package qtismtest\common\beans
 */
class BeanPropertyTest extends QtiSmTestCase
{
    public function testNoProperty()
    {
        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("The class property with name 'prop' does not exist in class 'stdClass'.");
        $this->expectExceptionCode(BeanException::NO_PROPERTY);

        $beanProperty = new BeanProperty(stdClass::class, 'prop');
    }

    public function testPropertyNotAnnotated()
    {
        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("The property with name 'anotherUselessProperty' for class '" . SimpleBean::class . "' is not annotated.");
        $this->expectExceptionCode(BeanException::NO_PROPERTY);

        $beanProperty = new BeanProperty(SimpleBean::class, 'anotherUselessProperty');
    }
}
