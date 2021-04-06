<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\BlockStaticCollection;
use qtism\data\content\FlowCollection;
use qtism\data\content\interactions\HottextInteraction;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtismtest\QtiSmTestCase;

/**
 * Class HottextInteractionTest
 */
class HottextInteractionTest extends QtiSmTestCase
{
    public function testSetMinChoicesValidValueWhenMaxChoicesIsZero()
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('content...')]));
        $hottextInteraction = new HottextInteraction('RESPONSE', new BlockStaticCollection([$div]));

        $hottextInteraction->setMaxChoices(0);
        $hottextInteraction->setMinChoices(2);

        self::assertSame(2, $hottextInteraction->getMinChoices());
    }
}
