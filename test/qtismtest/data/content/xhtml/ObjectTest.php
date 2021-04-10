<?php

namespace qtismtest\data\content\xhtml;

use InvalidArgumentException;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class ObjectTest
 */
class ObjectTest extends QtiSmTestCase
{
    public function testCreateWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'type' argument must be a non-empty string, 'integer' given.");

        new ObjectElement('./my-image.png', 999);
    }

    public function testSetWidthWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'width' argument must be an integer, 'double' given.");

        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setWidth(999.999);
    }

    public function testSetHeightWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'height' argument must be an integer, 'double' given.");

        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setHeight(999.999);
    }
}
