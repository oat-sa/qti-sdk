<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InfoControl;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Em;
use qtismtest\QtiSmTestCase;

/**
 * Class InfoControlMarshallerTest
 */
class InfoControlMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMinimal()
    {
        $component = new InfoControl();
        $component->setXmlBase('/home/jerome');
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($component)->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals(0, $element->childNodes->length);
        $this::assertEquals('', $element->getAttribute('id'));
        $this::assertEquals('', $element->getAttribute('class'));
        $this::assertEquals('', $element->getAttribute('lang'));
        $this::assertEquals('', $element->getAttribute('label'));
        $this::assertEquals('', $element->getAttribute('title'));
        $this::assertEquals('/home/jerome', $element->getAttribute('xml:base'));
    }

    public function testMarshallMinimalWithAttributes()
    {
        $component = new InfoControl('myControl', 'myInfo elt', 'en-US', 'A label...');
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($component)->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals(0, $element->childNodes->length);
        $this::assertEquals('myControl', $element->getAttribute('id'));
        $this::assertEquals('myInfo elt', $element->getAttribute('class'));
        $this::assertEquals('en-US', $element->getAttributeNS('http://www.w3.org/XML/1998/namespace', 'lang'));
        $this::assertEquals('A label...', $element->getAttribute('label'));
    }

    public function testUnmarshallMinimal()
    {
        $element = $this->createDOMElement('<infoControl title=""/>');
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(InfoControl::class, $component);
        $this::assertCount(0, $component->getComponents());
        $this::assertFalse($component->hasId());
        $this::assertFalse($component->hasClass());
        $this::assertFalse($component->hasLang());
        $this::assertFalse($component->hasLabel());
    }

    public function testUnmarshallMinimalWithAttributes()
    {
        $element = $this->createDOMElement('<infoControl id="myControl" class="myInfo elt" xml:lang="en-US" label="A label..." title=""/>');
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(InfoControl::class, $component);
        $this::assertCount(0, $component->getComponents());
        $this::assertEquals('myControl', $component->getId());
        $this::assertEquals('myInfo elt', $component->getClass());
        $this::assertEquals('en-US', $component->getLang());
        $this::assertEquals('A label...', $component->getLabel());
    }

    public function testUnmarshallComplex()
    {
        $element = $this->createDOMElement('
	        <infoControl id="controlMePlease" title="" xml:base="/home/jerome">
	            This is <em>gooood</em> !
	        </infoControl>
	    ');
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(InfoControl::class, $component);
        $this::assertEquals('controlMePlease', $component->getId());
        $content = $component->getContent();
        $this::assertCount(3, $content);
        $this::assertEquals('/home/jerome', $component->getXmlBase());

        $this::assertInstanceOf(TextRun::class, $content[0]);
        $this::assertEquals('This is ', ltrim($content[0]->getContent()));

        $this::assertInstanceOf(Em::class, $content[1]);
        $emContent = $content[1]->getContent();
        $this::assertCount(1, $emContent);
        $this::assertEquals('gooood', $emContent[0]->getContent());

        $this::assertInstanceOf(TextRun::class, $content[2]);
        $this::assertEquals(' !', rtrim($content[2]->getContent()));
    }

    public function testMarshallComplex()
    {
        $component = new InfoControl('controlMePlease');
        $component->setId('controlMePlease');

        $em = new Em();
        $em->setContent(new InlineCollection([new TextRun('gooood')]));

        $component->setContent(new FlowStaticCollection([new TextRun('This is '), $em, new TextRun(' !')]));
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($component)->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<infoControl id="controlMePlease" title="">This is <em>gooood</em> !</infoControl>', $dom->saveXML($element));
    }
}
