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

/**
 * Class PositionObjectInteractionMarshallerTest
 */
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
}
