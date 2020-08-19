<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

class ObjectTest extends QtiSmTestCase
{
    public function testCreateWrongData()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'data' argument must be a URI or an empty string, 'integer' given."
        );

        new ObjectElement(999, 'image/png');
    }

    public function testCreateWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'type' argument must be a non-empty string, 'integer' given."
        );

        new ObjectElement('./my-image.png', 999);
    }

    public function testSetWidthWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'width' argument must be an integer, 'double' given."
        );

        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setWidth(999.999);
    }

    public function testSetHeightWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'height' argument must be an integer, 'double' given."
        );

        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setHeight(999.999);
    }
}
