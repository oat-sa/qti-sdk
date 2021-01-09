<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\content\interactions\AssociableHotspotCollection;
use qtism\data\content\interactions\GapImg;
use qtism\data\content\interactions\GapImgCollection;
use qtism\data\content\interactions\GraphicGapMatchInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class GraphicGapMatchInteractionMarshallerTest
 */
class GraphicGapMatchInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $object = new ObjectElement('myimg.png', 'image/png');

        $img1 = new ObjectElement('img1.png', 'image/png');
        $gapImg1 = new GapImg('gapImg1', 1, $img1);
        $img2 = new ObjectElement('img2.png', 'image/png');
        $gapImg2 = new GapImg('gapImg2', 1, $img2);
        $img3 = new ObjectElement('img3.png', 'image/png');
        $gapImg3 = new GapImg('gapImg3', 1, $img3);
        $gapImgs = new GapImgCollection([$gapImg1, $gapImg2, $gapImg3]);

        $choice1 = new AssociableHotspot('choice1', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [0, 0, 15]));
        $choice2 = new AssociableHotspot('choice2', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [2, 2, 15]));
        $choice3 = new AssociableHotspot('choice3', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [4, 4, 15]));
        $choices = new AssociableHotspotCollection([$choice1, $choice2, $choice3]);

        $graphicGapMatchInteraction = new GraphicGapMatchInteraction('RESPONSE', $object, $gapImgs, $choices, 'my-gaps');
        $graphicGapMatchInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($graphicGapMatchInteraction)->marshall($graphicGapMatchInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<graphicGapMatchInteraction id="my-gaps" responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><object data="myimg.png" type="image/png"/><gapImg identifier="gapImg1" matchMax="1"><object data="img1.png" type="image/png"/></gapImg><gapImg identifier="gapImg2" matchMax="1"><object data="img2.png" type="image/png"/></gapImg><gapImg identifier="gapImg3" matchMax="1"><object data="img3.png" type="image/png"/></gapImg><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMax="1"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicGapMatchInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <graphicGapMatchInteraction id="my-gaps" responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><object data="myimg.png" type="image/png"/><gapImg identifier="gapImg1" matchMax="1"><object data="img1.png" type="image/png"/></gapImg><gapImg identifier="gapImg2" matchMax="1"><object data="img2.png" type="image/png"/></gapImg><gapImg identifier="gapImg3" matchMax="1"><object data="img3.png" type="image/png"/></gapImg><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMax="1"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicGapMatchInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(GraphicGapMatchInteraction::class, $component);
        $this::assertEquals('my-gaps', $component->getId());
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());

        $this::assertEquals('myimg.png', $component->getObject()->getData());
        $this::assertEquals('image/png', $component->getObject()->getType());

        $this::assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this::assertEquals('Prompt...', $promptContent[0]->getContent());

        $choices = $component->getAssociableHotspots();
        $this::assertEquals(3, count($choices));

        $this::assertEquals('choice1', $choices[0]->getIdentifier());
        $this::assertEquals('choice2', $choices[1]->getIdentifier());
        $this::assertEquals('choice3', $choices[2]->getIdentifier());

        $gapImgs = $component->getGapImgs();
        $this::assertEquals(3, count($gapImgs));

        $this::assertEquals('gapImg1', $gapImgs[0]->getIdentifier());
        $this::assertEquals('gapImg2', $gapImgs[1]->getIdentifier());
        $this::assertEquals('gapImg3', $gapImgs[2]->getIdentifier());
    }
}
