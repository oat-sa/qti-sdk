<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowCollection;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\TemplateBlock;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateBlockMarshallerTest
 */
class TemplateBlockMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $templateBlock = new TemplateBlock('tpl1', 'block1');
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('Templatable...')]));
        $templateBlock->setContent(new FlowStaticCollection([$div]));

        $element = $this->getMarshallerFactory()->createMarshaller($templateBlock)->marshall($templateBlock);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show"><div>Templatable...</div></templateBlock>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = self::createDOMElement('
	        <templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show"><div>Templatable...</div></templateBlock>
	    ');

        $component = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(TemplateBlock::class, $component);
        $this->assertEquals('tpl1', $component->getTemplateIdentifier());
        $this->assertEquals('block1', $component->getIdentifier());
        $this->assertEquals(ShowHide::SHOW, $component->getShowHide());
    }
}
