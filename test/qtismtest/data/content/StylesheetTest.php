<?php

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\Stylesheet;
use qtismtest\QtiSmTestCase;

class StylesheetTest extends QtiSmTestCase
{
    public function testCreateWrongHref()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Href must be a string, 'integer' given.");

        $stylesheet = new Stylesheet(999);
    }

    public function testSetInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Type must be a string, 'integer' given.");

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setType(999);
    }

    public function testSetInvalidMedia()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Media must be a string, 'integer' given.");

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setMedia(999);
    }

    public function testSetInvalidTitle()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Title must be a string, 'integer' given.");

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setTitle(999);
    }
}
