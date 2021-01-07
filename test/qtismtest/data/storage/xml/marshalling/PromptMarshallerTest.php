<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\A;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtismtest\QtiSmTestCase;

/**
 * Class PromptMarshallerTest
 */
class PromptMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new Prompt('my-prompt', 'qti-prompt');
        $component->setContent(new FlowStaticCollection([new TextRun('This is a prompt')]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<prompt id="my-prompt" class="qti-prompt">This is a prompt</prompt>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('<prompt id="my-prompt" class="qti-prompt">This is a prompt</prompt>');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Prompt::class, $component);
        $this->assertEquals('my-prompt', $component->getId());
        $this->assertEquals('qti-prompt', $component->getClass());

        $content = $component->getContent();
        $this->assertEquals(1, count($content));
        $this->assertEquals('This is a prompt', $content[0]->getContent());
    }

    public function testUnmarshallPromptWithAnchorInQti21ThrowsException()
    {
        $element = $this->createDOMElement('<prompt id="my-prompt" class="qti-prompt">This is an anchor: <a href="#">anchor text</a></prompt>');

        $marshaller = $this->getMarshallerFactory('2.1')->createMarshaller($element);
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'prompt' cannot contain 'a' elements.");
        $marshaller->unmarshall($element);
    }

    public function testUnmarshallPromptWithAnchorInQti22Works()
    {
        $element = $this->createDOMElement('<prompt id="my-prompt" class="qti-prompt">This is an anchor: <a href="#">anchor text</a></prompt>');

        $marshaller = $this->getMarshallerFactory('2.2')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Prompt::class, $component);
        $this->assertEquals('my-prompt', $component->getId());
        $this->assertEquals('qti-prompt', $component->getClass());

        $content = $component->getContent();
        $this->assertCount(2, $content);
        $this->assertEquals('This is an anchor: ', $content[0]->getContent());

        $this->assertInstanceOf(A::class, $content[1]);
        $this->assertEquals('#', $content[1]->getHref());

        $this->assertInstanceOf(InlineCollection::class, $content[1]->getContent());
        $linkContent = $content[1]->getContent()[0];
        $this->assertInstanceOf(TextRun::class, $linkContent);
        $this->assertEquals('anchor text', $linkContent->getContent());
    }
}
