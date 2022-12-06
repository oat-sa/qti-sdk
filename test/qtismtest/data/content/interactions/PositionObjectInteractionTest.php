<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\FlowCollection;
use qtism\data\content\interactions\PositionObjectInteraction;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\content\xhtml\text\Div;
use qtismtest\QtiSmTestCase;

/**
 * Class PositionObjectInteractionTest
 */
class PositionObjectInteractionTest extends QtiSmTestCase
{
    public function testSetMinChoicesValidValueWhenMaxChoicesIsZero(): void
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('content...')]));
        $object = new ObjectElement('myimg.jpg', 'image/jpeg');
        $object->setWidth(400);
        $object->setHeight(300);

        $positionObjectInteraction = new PositionObjectInteraction('RESPONSE', $object, 'my-pos');

        $positionObjectInteraction->setMaxChoices(0);
        $positionObjectInteraction->setMinChoices(2);

        self::assertSame(2, $positionObjectInteraction->getMinChoices());
    }
}
