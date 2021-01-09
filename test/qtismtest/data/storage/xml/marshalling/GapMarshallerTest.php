<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\collections\IdentifierCollection;
use qtism\data\content\interactions\Gap;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class GapMarshallerTest
 */
class GapMarshallerTest extends QtiSmTestCase
{
    public function testMarshall20()
    {
        $gap = new Gap('gap1', true, 'my-gap', 'gaps');
        $gap->setFixed(true);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($gap);
        $element = $marshaller->marshall($gap);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<gap identifier="gap1" fixed="true" id="my-gap" class="gaps"/>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallNoTemplateIdentifierShowHideRequiredNoOutput20()
    {
        $gap = new Gap('gap1');
        $gap->setTemplateIdentifier('XTEMPLATE');
        $gap->setRequired(true);
        $gap->setShowHide(ShowHide::HIDE);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($gap);
        $element = $marshaller->marshall($gap);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<gap identifier="gap1"/>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallMatchGroup20()
    {
        $gap = new Gap('gap1');
        $gap->setMatchGroup(new IdentifierCollection(['identifier1', 'identifier2']));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($gap);
        $element = $marshaller->marshall($gap);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<gap identifier="gap1" matchGroup="identifier1 identifier2"/>', $dom->saveXML($element));
    }

    public function testMarshall21()
    {
        $gap = new Gap('gap1', true, 'my-gap', 'gaps');
        $gap->setFixed(false);
        $gap->setShowHide(ShowHide::HIDE);
        $gap->setTemplateIdentifier('tpl-gap');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gap);
        $element = $marshaller->marshall($gap);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<gap identifier="gap1" templateIdentifier="tpl-gap" showHide="hide" required="true" id="my-gap" class="gaps"/>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshallNoMatchGroup21()
    {
        // Aims at testing that no matchGroup attribute is in
        // the output in a QTI 2.1 context.
        $gap = new Gap('gap1');
        $gap->setMatchGroup(new IdentifierCollection(['identifier1']));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gap);
        $element = $marshaller->marshall($gap);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<gap identifier="gap1"/>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <gap identifier="gap1" templateIdentifier="tpl-gap" required="true" id="my-gap" class="gaps" showHide="hide"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $gap = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Gap::class, $gap);
        $this->assertEquals('gap1', $gap->getIdentifier());
        $this->assertEquals('tpl-gap', $gap->getTemplateIdentifier());
        $this->assertTrue($gap->hasTemplateIdentifier());
        $this->assertTrue($gap->isRequired());
        $this->assertEquals('gaps', $gap->getClass());
        $this->assertEquals(ShowHide::HIDE, $gap->getShowHide());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshallMatchGroupNoInfluence21()
    {
        // Aims at testing that no matchGroup is in output in a QTI 2.1 context.
        $element = $this->createDOMElement('
	        <gap identifier="gap1" matchGroup="identifier1 identifier2"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $gap = $marshaller->unmarshall($element);
        $this->assertSame(0, count($gap->getMatchGroup()));
    }

    public function testUnmarshall20()
    {
        $element = $this->createDOMElement('
	        <gap identifier="gap1" fixed="true" id="my-gap" matchGroup="identifier1 identifier2" class="gaps"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $gap = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Gap::class, $gap);
        $this->assertEquals('gap1', $gap->getIdentifier());
        $this->assertEquals('', $gap->getTemplateIdentifier());
        $this->assertFalse($gap->hasTemplateIdentifier());
        $this->assertFalse($gap->isRequired());
        $this->assertEquals('gaps', $gap->getClass());
        $this->assertEquals(ShowHide::SHOW, $gap->getShowHide());
        $this->assertTrue($gap->isFixed());
        $this->assertEquals(['identifier1', 'identifier2'], $gap->getMatchGroup()->getArrayCopy());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallNoNoInfluenceRequiredTemplateIdentifierShowHide20()
    {
        // Aims at testing that required, templateIdentifier, showHide
        // attributes are not in output in a QTI 2.0 context.
        $element = $this->createDOMElement('
	        <gap identifier="gap1" required="true" templateIdentifier="XTEMPLATE" showHide="hide"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $gap = $marshaller->unmarshall($element);

        $this->assertFalse($gap->isRequired());
        $this->assertFalse($gap->hasTemplateIdentifier());
        $this->assertSame(ShowHide::SHOW, $gap->getShowHide());
    }
}
