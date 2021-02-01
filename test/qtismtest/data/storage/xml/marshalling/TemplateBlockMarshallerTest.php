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
use qtism\data\storage\xml\marshalling\UnmarshallingException;

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

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateBlock)->marshall($templateBlock);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show"><div>Templatable...</div></templateBlock>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall
     */
    public function testMarshallXmlBase()
    {
        $templateBlock = new TemplateBlock('tpl1', 'block1');
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('Templatable...')]));
        $templateBlock->setContent(new FlowStaticCollection([$div]));
        $templateBlock->setXmlBase('/home/jerome');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateBlock)->marshall($templateBlock);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show" xml:base="/home/jerome"><div>Templatable...</div></templateBlock>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show"><div>Templatable...</div></templateBlock>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateBlock::class, $component);
        $this::assertEquals('tpl1', $component->getTemplateIdentifier());
        $this::assertEquals('block1', $component->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $component->getShowHide());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidShowHide()
    {
        $element = $this->createDOMElement('
	        <templateBlock templateIdentifier="tpl1" identifier="block1" showHide="snow"><div>Templatable...</div></templateBlock>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("'snow' is not a valid value for the 'showHide' attribute of element 'templateBlock'.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidContent()
    {
        $element = $this->createDOMElement('
	        <templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show"><hottext identifier="myhottext"/></templateBlock>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The 'templateBlock' cannot contain 'hottext' elements.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoTemplateIdentifier()
    {
        $element = $this->createDOMElement('
	        <templateBlock identifier="block1" showHide="show">Templatable...</templateBlock>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'templateIdentifier' attribute is missing from element 'templateBlock'.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallXmlBase()
    {
        $element = $this->createDOMElement('
	        <templateBlock templateIdentifier="tpl1" identifier="block1" showHide="show" xml:base="/home/jerome"><div>Templatable...</div></templateBlock>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertEquals('/home/jerome', $component->getXmlBase());
    }
}
