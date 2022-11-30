<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\BlockCollection;
use qtism\data\content\FlowCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\ItemBody;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtism\data\content\xhtml\text\H1;
use qtismtest\QtiSmTestCase;

/**
 * Class ItemBodyMarshallerTest
 */
class ItemBodyMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall(): void
    {
        $itemBody = $this->createComponentFromXml('
            <itemBody id="my-body">
                <h1>Super Item</h1>
                <div>This is some stimulus.</div>   
            </itemBody>
        ');

        $this::assertInstanceOf(ItemBody::class, $itemBody);
        $this::assertEquals('my-body', $itemBody->getId());
        $itemBodyContent = $itemBody->getContent();
        $this::assertCount(2, $itemBodyContent);
        $this::assertInstanceOf(H1::class, $itemBodyContent[0]);
        $this::assertInstanceOf(Div::class, $itemBodyContent[1]);

        $h1 = $itemBodyContent[0];
        $h1Content = $h1->getContent();
        $this::assertCount(1, $h1Content);
        $this::assertEquals('Super Item', $h1Content[0]->getContent());

        $div = $itemBodyContent[1];
        $divContent = $div->getContent();
        $this::assertCount(1, $divContent);
        $this::assertEquals('This is some stimulus.', $divContent[0]->getContent());
    }

    public function testMarshall(): void
    {
        $h1 = new H1();
        $h1->setContent(new InlineCollection([new TextRun('Super Item')]));

        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('This is some stimulus.')]));

        $itemBody = new ItemBody('my-body');
        $itemBody->setContent(new BlockCollection([$h1, $div]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($itemBody)->marshall($itemBody);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<itemBody id="my-body"><h1>Super Item</h1><div>This is some stimulus.</div></itemBody>', $dom->saveXML($element));
    }
}
