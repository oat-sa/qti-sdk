<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\Choice;
use qtism\data\content\interactions\Hotspot;

/**
 * Class AssociableHotspotMarshallerTest
 */
class AssociableHotspotMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [92, 19, 261, 66]);
        $matchMax = 2;
        $matchMin = 1;
        $fixed = true;
        $showHide = ShowHide::HIDE;

        $associableHotspot = new AssociableHotspot('hotspot1', $matchMax, $shape, $coords, 'my-hot');
        $associableHotspot->setMatchMin($matchMin);
        $associableHotspot->setFixed($fixed);
        $associableHotspot->setShowHide($showHide);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($associableHotspot)->marshall($associableHotspot);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" fixed="true" showHide="hide" matchMax="2" matchMin="1" id="my-hot"/>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" fixed="true" showHide="hide" matchMax="2" matchMin="1" id="my-hot"/>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(AssociableHotspot::class, $component);
        $this::assertInstanceOf(Hotspot::class, $component);
        $this::assertInstanceOf(Choice::class, $component);

        $this::assertEquals('hotspot1', $component->getIdentifier());
        $this::assertEquals(QtiShape::RECT, $component->getShape());
        $this::assertEquals('92,19,261,66', $component->getCoords()->__toString());
        $this::assertTrue($component->isFixed());
        $this::assertEquals(ShowHide::HIDE, $component->getShowHide());
        $this::assertEquals(2, $component->getMatchMax());
        $this::assertEquals(1, $component->getMatchMin());
        $this::assertEquals('my-hot', $component->getId());
        $this::assertFalse($component->hasHotspotLabel());
    }
}
