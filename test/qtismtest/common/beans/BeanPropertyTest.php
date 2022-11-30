<?php

declare(strict_types=1);

namespace qtismtest\common\beans;

use qtism\common\beans\BeanException;
use qtism\common\beans\BeanProperty;
use qtismtest\common\beans\mocks\SimpleBean;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class BeanPropertyTest
 */
class BeanPropertyTest extends QtiSmTestCase
{
    public function testNoProperty(): void
    {
        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("The class property with name 'prop' does not exist in class 'stdClass'.");
        $this->expectExceptionCode(BeanException::NO_PROPERTY);

        $beanProperty = new BeanProperty(stdClass::class, 'prop');
    }

    public function testPropertyNotAnnotated(): void
    {
        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("The property with name 'anotherUselessProperty' for class '" . SimpleBean::class . "' is not annotated.");
        $this->expectExceptionCode(BeanException::NO_PROPERTY);

        $beanProperty = new BeanProperty(SimpleBean::class, 'anotherUselessProperty');
    }
}
