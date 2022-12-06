<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
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
    public function testCreateNoContent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A HottextInteraction object must be composed of at least one BlockStatic object, none given.');

        new HottextInteraction('RESPONSE', new BlockStaticCollection());
    }

    public function testSetMaxChoicesInvalidValue(): void
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('content...')]));
        $hottextInteraction = new HottextInteraction('RESPONSE', new BlockStaticCollection([$div]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument must be a positive (>= 0) integer, 'boolean' given.");

        $hottextInteraction->setMaxChoices(true);
    }

    public function testSetMinChoicesInvalidValue(): void
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('content...')]));
        $hottextInteraction = new HottextInteraction('RESPONSE', new BlockStaticCollection([$div]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must be a positive (>= 0) integer value, 'boolean' given.");

        $hottextInteraction->setMinChoices(true);
    }

    public function testSetMinChoicesInvalidValueRegardingMaxChoices(): void
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('content...')]));
        $hottextInteraction = new HottextInteraction('RESPONSE', new BlockStaticCollection([$div]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must respect the limits imposed by 'maxChoices'.");

        $hottextInteraction->setMaxChoices(1);
        $hottextInteraction->setMinChoices(2);
    }

    public function testSetMinChoicesValidValueWhenMaxChoicesIsZero(): void
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('content...')]));
        $hottextInteraction = new HottextInteraction('RESPONSE', new BlockStaticCollection([$div]));

        $hottextInteraction->setMaxChoices(0);
        $hottextInteraction->setMinChoices(2);

        $this->assertSame(2, $hottextInteraction->getMinChoices());
    }
}
