<?php

namespace qtismtest\data\content;

use qtism\data\content\Stylesheet;
use qtismtest\QtiSmTestCase;

class StylesheetTest extends QtiSmTestCase
{
    public function testCreateWrongHref()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Href must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet(999);
    }

    public function testSetInvalidType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Type must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setType(999);
    }

    public function testSetInvalidMedia()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Media must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setMedia(999);
    }

    public function testSetInvalidTitle()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Title must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setTitle(999);
    }
}
