<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\BlockStaticCollection;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\Gap;
use qtism\data\content\interactions\GapChoiceCollection;
use qtism\data\content\interactions\GapImg;
use qtism\data\content\interactions\GapMatchInteraction;
use qtism\data\content\interactions\GapText;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\QtiObject;
use qtism\data\content\xhtml\text\P;
use qtismtest\QtiSmTestCase;

/**
 * Class GapMatchInteractionMarshallerTest
 */
class GapMatchInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $gapText = new GapText('gapText1', 1);
        $gapText->setContent(new FlowStaticCollection([new TextRun('This is gapText1')]));

        $object = new QtiObject('./myimg.png', 'image/png');
        $gapImg = new GapImg('gapImg1', 1, $object);

        $gap1 = new Gap('G1');
        $gap2 = new Gap('G2');

        $p = new P();
        $p->setContent(new InlineCollection([new TextRun('A text... '), $gap1, new TextRun(' and an image... '), $gap2]));

        $gapMatch = new GapMatchInteraction('RESPONSE', new GapChoiceCollection([$gapText, $gapImg]), new BlockStaticCollection([$p]));

        $marshaller = $this->getMarshallerFactory()->createMarshaller($gapMatch);
        $element = $marshaller->marshall($gapMatch);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<gapMatchInteraction responseIdentifier="RESPONSE"><gapText identifier="gapText1" matchMax="1">This is gapText1</gapText><gapImg identifier="gapImg1" matchMax="1"><object data="./myimg.png" type="image/png"/></gapImg><p>A text... <gap identifier="G1"/> and an image... <gap identifier="G2"/></p></gapMatchInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall()
    {
        $element = self::createDOMElement('
            <gapMatchInteraction responseIdentifier="RESPONSE"><gapText identifier="gapText1" matchMax="1">This is gapText1</gapText><gapImg identifier="gapImg1" matchMax="1"><object data="./myimg.png" type="image/png"/></gapImg><p>A text... <gap identifier="G1"/> and an image... <gap identifier="G2"/></p></gapMatchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $gapMatch = $marshaller->unmarshall($element);

        $this->assertInstanceOf(GapMatchInteraction::class, $gapMatch);
        $this->assertEquals('RESPONSE', $gapMatch->getResponseIdentifier());
        $this->assertFalse($gapMatch->mustShuffle());

        $gapChoices = $gapMatch->getGapChoices();
        $this->assertEquals(2, count($gapChoices));
        $this->assertInstanceOf(GapText::class, $gapChoices[0]);
        $this->assertInstanceOf(GapImg::class, $gapChoices[1]);

        $gaps = $gapMatch->getComponentsByClassName('gap');
        $this->assertEquals(2, count($gaps));
        $this->assertEquals('G1', $gaps[0]->getIdentifier());
        $this->assertEquals('G2', $gaps[1]->getIdentifier());
    }
}
