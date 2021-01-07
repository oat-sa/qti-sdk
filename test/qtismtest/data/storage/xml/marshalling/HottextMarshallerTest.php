<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\InlineStaticCollection;
use qtism\data\content\interactions\Hottext;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class HottextMarshallerTest
 */
class HottextMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $hottext = new Hottext('choice1', 'my-hottext1', 'so hot');
        $hottext->setFixed(true);
        $hottext->setShowHide(ShowHide::HIDE);
        $hottext->setTemplateIdentifier('tpl1');
        $hottext->setContent(new InlineStaticCollection([new TextRun('Choice1')]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($hottext)->marshall($hottext);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<hottext id="my-hottext1" class="so hot" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide">Choice1</hottext>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <hottext id="my-hottext1" class="so hot" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide">Choice1</hottext>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(Hottext::class, $component);
        $this->assertEquals('my-hottext1', $component->getId());
        $this->assertEquals('so hot', $component->getClass());
        $this->assertEquals('choice1', $component->getIdentifier());
        $this->assertTrue($component->isFixed());
        $this->assertEquals('tpl1', $component->getTemplateIdentifier());
        $this->assertEquals(ShowHide::HIDE, $component->getShowHide());
        $content = $component->getContent();
        $this->assertEquals('Choice1', $content[0]->getContent());
    }
}
