<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\ObjectFlowCollection;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\content\xhtml\Param;
use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmTestCase;

/**
 * Class ObjectMarshallerTest
 */
class ObjectMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshallSimple(): void
    {
        /** @var ObjectElement $object */
        $object = $this->createComponentFromXml('
	        <object id="flash-movie" data="http://mywebsite.com/movie.swf" type="application/x-shockwave-flash">
	            <param name="movie" value="movie.swf" valuetype="REF"/>
	            <param name="quality" value="high" valuetype="DATA"/>
	        </object>                
	    ');

        $this::assertInstanceOf(ObjectElement::class, $object);
        $this::assertEquals('flash-movie', $object->getId());
        $this::assertEquals('http://mywebsite.com/movie.swf', $object->getData());
        $this::assertEquals('application/x-shockwave-flash', $object->getType());

        $objectContent = $object->getContent();
        $this::assertCount(5, $objectContent); // Including text nodes.

        $param1 = $objectContent[1];
        $this::assertInstanceOf(Param::class, $param1);
        $this::assertEquals('movie', $param1->getName());
        $this::assertEquals('movie.swf', $param1->getValue());
        $this::assertEquals(ParamType::REF, $param1->getValueType());

        $param2 = $objectContent[3];
        $this::assertInstanceOf(Param::class, $param2);
        $this::assertEquals('quality', $param2->getName());
        $this::assertEquals('high', $param2->getValue());
        $this::assertEquals(ParamType::DATA, $param2->getValueType());

        $this::assertFalse($object->hasHeight());
        $this::assertFalse($object->hasWidth());
    }

    public function testUnmarshallNoDataAttributeValue(): void
    {
        $object = $this->createComponentFromXml('
	        <object id="flash-movie" data="" type="application/x-shockwave-flash"/>
	    ');

        $this::assertInstanceOf(ObjectElement::class, $object);
        $this::assertEquals('flash-movie', $object->getId());
        $this::assertEquals('', $object->getData());
        $this::assertEquals('application/x-shockwave-flash', $object->getType());
    }

    public function testUnmarshallWithDimensionPercentAttributesValue(): void
    {
        /** @var ObjectElement $object */
        $object = $this->createComponentFromXml('
	        <object id="flash-movie" width="100%" height="10%" data="" type="application/x-shockwave-flash"/>
	    ');

        $this::assertTrue($object->hasWidth());
        $this::assertTrue($object->hasHeight());
        $this::assertEquals('10%', $object->getHeight());
        $this::assertEquals('100%', $object->getWidth());
    }

    public function testUnmarshallWithDimensionIntegerAttributesValue(): void
    {
        /** @var ObjectElement $object */
        $object = $this->createComponentFromXml('
	        <object id="flash-movie" width="1000" height="1" data="" type="application/x-shockwave-flash"/>
	    ');

        $this::assertTrue($object->hasWidth());
        $this::assertTrue($object->hasHeight());
        $this::assertEquals('1', $object->getHeight());
        $this::assertEquals('1000', $object->getWidth());
    }

    public function testMarshallSimple(): void
    {
        $param1 = new Param('movie', 'movie.swf', ParamType::REF);
        $param2 = new Param('quality', 'high', ParamType::DATA);
        $object = new ObjectElement('http://mywebsite.com/movie.swf', 'application/x-shockwave-flash', 'flash-movie');
        $object->setContent(new ObjectFlowCollection([$param1, $param2]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($object)->marshall($object);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<object data="http://mywebsite.com/movie.swf" type="application/x-shockwave-flash" id="flash-movie"><param name="movie" value="movie.swf" valuetype="REF"/><param name="quality" value="high" valuetype="DATA"/></object>', $dom->saveXml($element));
    }

    public function testMarshallNoDataAttributeValue(): void
    {
        $object = new ObjectElement('', 'application/x-shockwave-flash', 'flash-movie');
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($object)->marshall($object);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<object data="" type="application/x-shockwave-flash" id="flash-movie"/>', $dom->saveXml($element));
    }
}
