<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiPoint;
use qtism\data\content\interactions\PositionObjectInteraction;
use qtism\data\content\interactions\PositionObjectInteractionCollection;
use qtism\data\content\interactions\PositionObjectStage;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class PositionObjectStageMarshallerTest
 */
class PositionObjectStageMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $interactionObject = new ObjectElement('airplane.jpg', 'image/jpeg');
        $interactionObject->setHeight(16);
        $interactionObject->setWidth(16);

        $interaction = new PositionObjectInteraction('RESPONSE', $interactionObject);
        $interaction->setCenterPoint(new QtiPoint(8, 8));

        $stageObject = new ObjectElement('country.jpg', 'image/jpeg');
        $positionObjectStage = new PositionObjectStage($stageObject, new PositionObjectInteractionCollection([$interaction]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($positionObjectStage)->marshall($positionObjectStage);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<positionObjectStage><object data="country.jpg" type="image/jpeg"/><positionObjectInteraction responseIdentifier="RESPONSE" centerPoint="8 8"><object data="airplane.jpg" type="image/jpeg" width="16" height="16"/></positionObjectInteraction></positionObjectStage>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <positionObjectStage>
                <object data="country.jpg" type="image/jpeg"/>
                <positionObjectInteraction responseIdentifier="RESPONSE" maxChoices="1" centerPoint="8 8">
                    <object data="airplane.jpg" type="image/jpeg" width="16" height="16"/>
                </positionObjectInteraction>
            </positionObjectStage>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(PositionObjectStage::class, $component);

        $object = $component->getObject();
        $this::assertEquals('country.jpg', $object->getData());
        $this::assertEquals('image/jpeg', $object->getType());

        $interactions = $component->getPositionObjectInteractions();
        $this::assertCount(1, $interactions);

        $interaction = $interactions[0];
        $this::assertEquals('RESPONSE', $interaction->getResponseIdentifier());
        $this::assertEquals(1, $interaction->getMaxChoices());
        $this::assertTrue($interaction->getCenterPoint()->equals(new QtiPoint(8, 8)));

        $interactionObject = $interaction->getObject();
        $this::assertEquals('airplane.jpg', $interactionObject->getData());
        $this::assertEquals('image/jpeg', $interactionObject->getType());
        $this::assertEquals(16, $interactionObject->getWidth());
        $this::assertEquals(16, $interactionObject->getHeight());
    }
}
