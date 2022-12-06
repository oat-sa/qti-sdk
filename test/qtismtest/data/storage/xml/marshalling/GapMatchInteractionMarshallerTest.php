<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\BlockStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\Gap;
use qtism\data\content\interactions\GapChoiceCollection;
use qtism\data\content\interactions\GapImg;
use qtism\data\content\interactions\GapMatchInteraction;
use qtism\data\content\interactions\GapText;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\content\xhtml\text\P;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class GapMatchInteractionMarshallerTest
 */
class GapMatchInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $gapText = new GapText('gapText1', 1);
        $gapText->setContent(new TextOrVariableCollection([new TextRun('This is gapText1')]));

        $object = new ObjectElement('./myimg.png', 'image/png');
        $gapImg = new GapImg('gapImg1', 1, $object);

        $gap1 = new Gap('G1');
        $gap2 = new Gap('G2');

        $p = new P();
        $p->setContent(new InlineCollection([new TextRun('A text... '), $gap1, new TextRun(' and an image... '), $gap2]));

        $gapMatch = new GapMatchInteraction('RESPONSE', new GapChoiceCollection([$gapText, $gapImg]), new BlockStaticCollection([$p]));
        $gapMatch->setXmlBase('/home/jerome');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapMatch);
        $element = $marshaller->marshall($gapMatch);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<gapMatchInteraction responseIdentifier="RESPONSE" xml:base="/home/jerome"><gapText identifier="gapText1" matchMax="1">This is gapText1</gapText><gapImg identifier="gapImg1" matchMax="1"><object data="./myimg.png" type="image/png"/></gapImg><p>A text... <gap identifier="G1"/> and an image... <gap identifier="G2"/></p></gapMatchInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall(): void
    {
        $element = $this->createDOMElement('
            <gapMatchInteraction responseIdentifier="RESPONSE" xml:base="/home/jerome"><gapText identifier="gapText1" matchMax="1">This is gapText1</gapText><gapImg identifier="gapImg1" matchMax="1"><object data="./myimg.png" type="image/png"/></gapImg><p>A text... <gap identifier="G1"/> and an image... <gap identifier="G2"/></p></gapMatchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $gapMatch = $marshaller->unmarshall($element);

        $this::assertInstanceOf(GapMatchInteraction::class, $gapMatch);
        $this::assertEquals('RESPONSE', $gapMatch->getResponseIdentifier());
        $this::assertEquals('/home/jerome', $gapMatch->getXmlBase());
        $this::assertFalse($gapMatch->mustShuffle());

        $gapChoices = $gapMatch->getGapChoices();
        $this::assertCount(2, $gapChoices);
        $this::assertInstanceOf(GapText::class, $gapChoices[0]);
        $this::assertInstanceOf(GapImg::class, $gapChoices[1]);

        $gaps = $gapMatch->getComponentsByClassName('gap');
        $this::assertCount(2, $gaps);
        $this::assertEquals('G1', $gaps[0]->getIdentifier());
        $this::assertEquals('G2', $gaps[1]->getIdentifier());
    }

    public function testUnmarshallNoGapChoice(): void
    {
        $element = $this->createDOMElement('
            <gapMatchInteraction responseIdentifier="RESPONSE" xml:base="/home/jerome"><p>A text... <gap identifier="G1"/> and an image... <gap identifier="G2"/></p></gapMatchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'gapMatchInteraction' element must contain at least 1 'gapChoice' element, none given.");

        $marshaller->unmarshall($element);
    }

    public function testUnmarshallNoResponseIdentifier(): void
    {
        $element = $this->createDOMElement('
            <gapMatchInteraction xml:base="/home/jerome"><gapText identifier="gapText1" matchMax="1">This is gapText1</gapText><gapImg identifier="gapImg1" matchMax="1"><object data="./myimg.png" type="image/png"/></gapImg><p>A text... <gap identifier="G1"/> and an image... <gap identifier="G2"/></p></gapMatchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'gapMatchInteraction' element.");

        $marshaller->unmarshall($element);
    }
}
