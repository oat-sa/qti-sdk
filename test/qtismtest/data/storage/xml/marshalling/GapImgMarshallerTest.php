<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\GapImg;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class GapImgMarshallerTest
 */
class GapImgMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $object = new ObjectElement('http://imagine.us/myimg.png', 'image/png');
        $gapImg = new GapImg('gapImg1', 1, $object, 'my-gap', 'gaps');
        $gapImg->setShowHide(ShowHide::HIDE);
        $gapImg->setMatchMin(1);
        $gapImg->setTemplateIdentifier('templateIdentifier1');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapImg);
        $element = $marshaller->marshall($gapImg);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<gapImg id="my-gap" class="gaps" identifier="gapImg1" matchMax="1" matchMin="1" templateIdentifier="templateIdentifier1" showHide="hide"><object data="http://imagine.us/myimg.png" type="image/png"/></gapImg>',
            $dom->saveXML($element)
        );
    }

    /*
     * @depends testMarshall21
     */
    public function testMarshallFixed()
    {
        $object = new ObjectElement('http://imagine.us/myimg.png', 'image/png');
        $gapImg = new GapImg('gapImg1', 0, $object);
        $gapImg->setFixed(true);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapImg);
        $element = $marshaller->marshall($gapImg);

        $this::assertEquals('true', $element->getAttribute('fixed'));
    }

    /*
     * @depends testMarshall21
     */
    public function testMarshallObjectLabel()
    {
        $object = new ObjectElement('http://imagine.us/myimg.png', 'image/png');
        $gapImg = new GapImg('gapImg1', 0, $object);
        $gapImg->setObjectLabel('My Label');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapImg);
        $element = $marshaller->marshall($gapImg);

        $this::assertEquals('My Label', $element->getAttribute('objectLabel'));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <gapImg id="my-gap" class="gaps" identifier="gapImg1" matchMax="1">
              <object data="http://imagine.us/myimg.png" type="image/png"/>
            </gapImg>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $gapImg = $marshaller->unmarshall($element);

        $this::assertInstanceOf(GapImg::class, $gapImg);
        $this::assertEquals('my-gap', $gapImg->getId());
        $this::assertEquals('gaps', $gapImg->getClass());
        $this::assertEquals('gapImg1', $gapImg->getIdentifier());
        $this::assertEquals(0, $gapImg->getMatchMin());
        $this::assertEquals(1, $gapImg->getMatchMax());

        $object = $gapImg->getObject();
        $this::assertEquals('http://imagine.us/myimg.png', $object->getData());
        $this::assertEquals('image/png', $object->getType());
    }
}
