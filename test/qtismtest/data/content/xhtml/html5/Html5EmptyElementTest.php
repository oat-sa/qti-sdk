<?php

namespace qtismtest\data\content\xhtml\html5;

use qtism\data\content\xhtml\html5\Html5EmptyElement;
use qtism\data\QtiComponentCollection;
use qtismtest\QtiSmTestCase;

class Html5EmptyElementTest extends QtiSmTestCase
{
    public function testGetComponents()
    {
        $subject = new fakeHtml5EmptyElement();

        $this->assertEquals(new QtiComponentCollection(), $subject->getComponents());
    }
}

class FakeHtml5EmptyElement extends Html5EmptyElement
{
    public function getQtiClassName()
    {
    }
}
