<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\BlockStaticCollection;
use qtism\data\content\FlowCollection;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineStaticCollection;
use qtism\data\content\interactions\Hottext;
use qtism\data\content\interactions\HottextInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtismtest\QtiSmTestCase;

/**
 * Class HottextInteractionMarshallerTest
 */
class HottextInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $hottext = new Hottext('hottext1');
        $hottext->setContent(new InlineStaticCollection([new TextRun('hot')]));

        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('This is a '), new Hottext('hot1'), new TextRun(' text...')]));
        $content = new BlockStaticCollection([$div]);
        $hottextInteraction = new HottextInteraction('RESPONSE', $content);

        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $hottextInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory()->createMarshaller($hottextInteraction)->marshall($hottextInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<hottextInteraction responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><div>This is a <hottext identifier="hot1"/> text...</div></hottextInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <hottextInteraction responseIdentifier="RESPONSE">
                <prompt>Prompt...</prompt>
                <div>This is a <hottext identifier="hot1"/> text...</div>
            </hottextInteraction>
        ');

        $component = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(HottextInteraction::class, $component);
        $this->assertEquals(1, $component->getMaxChoices());
        $this->assertEquals(0, $component->getMinChoices());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());

        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());

        $content = $component->getContent();
        $div = $content[0];
        $this->assertInstanceOf(Div::class, $div);
        $divContent = $div->getContent();

        $this->assertInstanceOf(TextRun::class, $divContent[0]);
        $this->assertEquals('This is a ', $divContent[0]->getContent());

        $this->assertInstanceOf(Hottext::class, $divContent[1]);
        $this->assertEquals('hot1', $divContent[1]->getIdentifier());

        $this->assertInstanceOf(TextRun::class, $divContent[2]);
        $this->assertEquals(' text...', $divContent[2]->getContent());
    }
}
