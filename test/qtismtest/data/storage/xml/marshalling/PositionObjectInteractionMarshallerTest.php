<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiPoint;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\PositionObjectInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class PositionObjectInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $object = new ObjectElement('myimg.jpg', 'image/jpeg');
        $object->setWidth(400);
        $object->setHeight(300);

        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $positionObjectInteraction = new PositionObjectInteraction('RESPONSE', $object, 'my-pos');
        $positionObjectInteraction->setCenterPoint(new QtiPoint(150, 74));
        $positionObjectInteraction->setMaxChoices(2);
        $positionObjectInteraction->setMinChoices(1);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($positionObjectInteraction)->marshall($positionObjectInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" centerPoint="150 74" id="my-pos"><object data="myimg.jpg" type="image/jpeg" width="400" height="300"/></positionObjectInteraction>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshall20()
    {
        // Make sure minChoices is not taken into account in a QTI 2.0 context.
        $object = new ObjectElement('myimg.jpg', 'image/jpeg');
        $object->setWidth(400);
        $object->setHeight(300);

        $positionObjectInteraction = new PositionObjectInteraction('RESPONSE', $object);
        $positionObjectInteraction->setMaxChoices(2);
        $positionObjectInteraction->setMinChoices(1);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($positionObjectInteraction)->marshall($positionObjectInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2"><object data="myimg.jpg" type="image/jpeg" width="400" height="300"/></positionObjectInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" centerPoint="150 74" id="my-pos">
               <object data="myimg.jpg" type="image/jpeg" width="400" height="300"/>
            </positionObjectInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(PositionObjectInteraction::class, $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals(2, $component->getMaxChoices());
        $this->assertEquals(1, $component->getMinChoices());
        $this->assertTrue($component->getCenterPoint()->equals(new QtiPoint(150, 74)));
        $this->assertEquals('my-pos', $component->getId());

        $this->assertEquals('myimg.jpg', $component->getObject()->getData());
        $this->assertEquals('image/jpeg', $component->getObject()->getType());
        $this->assertEquals(400, $component->getObject()->getWidth());
        $this->assertEquals(300, $component->getObject()->getHeight());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21InvalidCenterPoint()
    {
        $element = $this->createDOMElement('
            <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" centerPoint="invalid" id="my-pos">
               <object data="myimg.jpg" type="image/jpeg" width="400" height="300"/>
            </positionObjectInteraction>
        ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "The value of the 'centePoint' attribute of a 'positionObjectInteraction' element must be composed of exactly 2 integer values, 1 given."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21InvalidCenterPointFirstValue()
    {
        $element = $this->createDOMElement('
            <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" centerPoint="invalid 74" id="my-pos">
               <object data="myimg.jpg" type="image/jpeg" width="400" height="300"/>
            </positionObjectInteraction>
        ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "The 1st value of the 'centerPoint' attribute value is not a valid integer for element 'positionObjectInteraction'."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21InvalidCenterPointSecondValue()
    {
        $element = $this->createDOMElement('
            <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" centerPoint="74 invalid" id="my-pos">
               <object data="myimg.jpg" type="image/jpeg" width="400" height="300"/>
            </positionObjectInteraction>
        ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "The 2nd integer of the 'centerPoint' attribute value is not a valid integer for element 'positionObjectInteraction'."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoObject()
    {
        $element = $this->createDOMElement('
            <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" centerPoint="74 invalid" id="my-pos"/>
        ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "A 'positionObjectInteraction' element must contain exactly one 'object' element, none given."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21MissingResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <positionObjectInteraction maxChoices="2" minChoices="1" centerPoint="74 invalid" id="my-pos">
                <object data="myimg.jpg" type="image/jpeg" width="400" height="300"/>
            </positionObjectInteraction>
        ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "The mandatory 'responseIdentifier' attribute is missing from the 'positionObjectInteraction' object."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall20()
    {
        // Make sure minChoices is not in output in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="2">
               <object data="myimg.jpg" type="image/jpeg" width="400" height="300"/>
            </positionObjectInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this->assertFalse($component->hasMinChoices());
    }
}
