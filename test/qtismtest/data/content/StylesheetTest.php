<?php

namespace qtismtest\data\content;

use qtism\data\content\Stylesheet;
use qtismtest\QtiSmTestCase;

class StylesheetTest extends QtiSmTestCase
{
    public function testCreateWrongHref()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Href must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet(999);
    }

    public function testSetInvalidType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Type must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setType(999);
    }

    public function testSetInvalidMedia()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Media must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setMedia(999);
    }

    public function testSetInvalidTitle()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Title must be a string, 'integer' given."
        );

        $stylesheet = new Stylesheet('style.css');
        $stylesheet->setTitle(999);
    }
}
