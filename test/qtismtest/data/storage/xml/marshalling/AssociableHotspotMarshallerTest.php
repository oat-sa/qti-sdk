<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

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
        $this->assertEquals('<associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" fixed="true" showHide="hide" matchMin="1" matchMax="2" id="my-hot"/>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshallNoOutputForDefaultMatchMinFixedShowHide21()
    {
        // Aims at testing that fixed, matchMin, showHide attributes are not
        // in the output if default values are set.
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [92, 19, 261, 66]);
        $matchMax = 0;

        $associableHotspot = new AssociableHotspot('hotspot1', $matchMax, $shape, $coords);
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($associableHotspot)->marshall($associableHotspot);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" matchMax="0"/>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshallNoOutputForMatchGroup21()
    {
        // Aims that testing that matchGroup is never in the output
        // in a QTI 2.1 context.
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [92, 19, 261, 66]);
        $matchMax = 0;

        $associableHotspot = new AssociableHotspot('hotspot1', $matchMax, $shape, $coords);
        $associableHotspot->setMatchGroup(new IdentifierCollection(['identifier1']));
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($associableHotspot)->marshall($associableHotspot);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        // No match group should appear!
        $this->assertEquals('<associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" matchMax="0"/>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" fixed="true" showHide="hide" matchMax="2" matchMin="1" id="my-hot"/>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(AssociableHotspot::class, $component);

        $this->assertEquals('hotspot1', $component->getIdentifier());
        $this->assertEquals(QtiShape::RECT, $component->getShape());
        $this->assertEquals('92,19,261,66', $component->getCoords()->__toString());
        $this->assertTrue($component->isFixed());
        $this->assertEquals(ShowHide::HIDE, $component->getShowHide());
        $this->assertEquals(2, $component->getMatchMax());
        $this->assertEquals(1, $component->getMatchMin());
        $this->assertEquals('my-hot', $component->getId());
        $this->assertFalse($component->hasHotspotLabel());
    }

    public function testUnmarshall21NoMatchMax()
    {
        $element = $this->createDOMElement('
	        <associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" fixed="true" showHide="hide" matchMin="1" id="my-hot"/>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'matchMax' is missing from element 'associableHotspot'");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testMarshall20()
    {
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [92, 19, 261, 66]);
        $matchMax = 2;
        $matchMin = 1;
        $fixed = true;
        $showHide = ShowHide::HIDE;

        $associableHotspot = new AssociableHotspot('hotspot1', $matchMax, $shape, $coords);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($associableHotspot)->marshall($associableHotspot);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" matchMax="2"/>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallMatchGroup20()
    {
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [92, 19, 261, 66]);
        $matchMax = 2;
        $matchMin = 1;
        $showHide = ShowHide::HIDE;
        $templateIdentifier = 'XTEMPLATE';

        $associableHotspot = new AssociableHotspot('hotspot1', $matchMax, $shape, $coords);
        $associableHotspot->setShowHide($showHide);
        $associableHotspot->setTemplateIdentifier($templateIdentifier);
        $associableHotspot->setMatchGroup(new IdentifierCollection(['identifier1', 'identifier2']));

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($associableHotspot)->marshall($associableHotspot);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" matchGroup="identifier1 identifier2" matchMax="2"/>', $dom->saveXML($element));
    }

    public function testUnmarshall20()
    {
        $element = $this->createDOMElement('
	        <associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" fixed="true" matchMax="0" id="my-hot" hotspotLabel="yeah"/>
	    ');

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(AssociableHotspot::class, $component);

        $this->assertEquals('hotspot1', $component->getIdentifier());
        $this->assertEquals(QtiShape::RECT, $component->getShape());
        $this->assertEquals('92,19,261,66', $component->getCoords()->__toString());
        $this->assertTrue($component->isFixed());
        $this->assertEquals(0, $component->getMatchMax());
        $this->assertEquals('my-hot', $component->getId());
        $this->assertEquals('yeah', $component->getHotspotLabel());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallMatchMinNoInfluenceMatchMinTemplateIdentifierShowHide20()
    {
        // Aims at testing that matchMin, templateIdentifier and showHide attributes have no influence
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
	        <associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" matchMax="2" matchMin="3"/>
	    ');

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);

        // Default values for the attributes.
        $this->assertEquals(ShowHide::SHOW, $component->getShowHide());
        $this->assertFalse($component->hasTemplateIdentifier());
        $this->assertEquals(0, $component->getMatchMin());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallMatchGroup20()
    {
        $element = $this->createDOMElement('
	        <associableHotspot identifier="hotspot1" shape="rect" coords="92,19,261,66" matchMax="2" matchGroup="identifier1 identifier2 identifier3"/>
	    ');

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);

        // Default values for the attributes.
        $this->assertEquals(['identifier1', 'identifier2', 'identifier3'], $component->getMatchGroup()->getArrayCopy());
    }
}
