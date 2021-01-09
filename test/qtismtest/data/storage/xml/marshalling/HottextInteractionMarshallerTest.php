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
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class HottextInteractionMarshallerTest
 */
class HottextInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $hottext = new Hottext('hottext1');
        $hottext->setContent(new InlineStaticCollection([new TextRun('hot')]));

        $div = new Div();
        $div->setContent(new FlowCollection([
            new TextRun('This is a '),
            new Hottext('hot1'),
            new TextRun(' text... '),
            new Hottext('hot2')
        ]));
        $content = new BlockStaticCollection([$div]);
        $hottextInteraction = new HottextInteraction('RESPONSE', $content);
        $hottextInteraction->setMinChoices(1);
        $hottextInteraction->setMaxChoices(2);
        $hottextInteraction->setXmlBase('/home/jerome');

        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $hottextInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($hottextInteraction)->marshall($hottextInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<hottextInteraction responseIdentifier="RESPONSE" maxChoices="2" minChoices="1" xml:base="/home/jerome"><prompt>Prompt...</prompt><div>This is a <hottext identifier="hot1"/> text... <hottext identifier="hot2"/></div></hottextInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshallNoOutputMinStrings20()
    {
        // Make sure no output for minStrings in a QTI 2.0 context.
        $hottext = new Hottext('hottext1');
        $hottext->setContent(new InlineStaticCollection([new TextRun('hot')]));

        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('This is a '), new Hottext('hot1'), new TextRun(' text...')]));
        $content = new BlockStaticCollection([$div]);
        $hottextInteraction = new HottextInteraction('RESPONSE', $content);
        $hottextInteraction->setMinChoices(1);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($hottextInteraction)->marshall($hottextInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<hottextInteraction responseIdentifier="RESPONSE"><div>This is a <hottext identifier="hot1"/> text...</div></hottextInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <hottextInteraction responseIdentifier="RESPONSE" xml:base="/home/jerome">
                <prompt>Prompt...</prompt>
                <div>This is a <hottext identifier="hot1"/> text...</div>
            </hottextInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(HottextInteraction::class, $component);
        $this->assertEquals(0, $component->getMaxChoices());
        $this->assertEquals(0, $component->getMinChoices());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals('/home/jerome', $component->getXmlBase());

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

    public function testUnmarshall21InvalidContent()
    {
        $element = $this->createDOMElement('
            <hottextInteraction responseIdentifier="RESPONSE">
                <prompt>Prompt...</prompt>
                <choiceInteraction responseIdentifier="RESPONSE">
                    <simpleChoice identifier="identifier">Choice A</simpleChoice>
                </choiceInteraction>
            </hottextInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The content of the 'hottextInteraction' element is invalid.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshall21InvalidResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <hottextInteraction responseIdentifier="999-RESPONSE">
                <prompt>Prompt...</prompt>
                <div>This is a <hottext identifier="hot1"/> text...</div>
            </hottextInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The value '999-RESPONSE' for the attribute 'responseIdentifier' for element 'hottextInteraction' is not a valid QTI identifier.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testNoInfluenceMinStrings20()
    {
        $element = $this->createDOMElement('
            <hottextInteraction responseIdentifier="RESPONSE">
                <div>This is a <hottext identifier="hot1" minChoices="2"/> text...</div>
            </hottextInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(HottextInteraction::class, $component);
        $this->assertEquals(0, $component->getMinChoices());
    }
}
