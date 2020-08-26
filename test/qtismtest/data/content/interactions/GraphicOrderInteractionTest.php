<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\interactions\GraphicOrderInteraction;
use qtism\data\content\interactions\HotspotChoice;
use qtism\data\content\interactions\HotspotChoiceCollection;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class GraphicOrderInteractionTest
 */
class GraphicOrderInteractionTest extends QtiSmTestCase
{
    public function testCreateNotEnoughHotspotChoices()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("A GraphicOrderInteraction must contain at least 1 hotspotChoice object. None given.");

        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', new ObjectElement('http://my-data/data.png', 'image/png'), new HotSpotChoiceCollection());
    }

    public function testTooLargeMinChoices()
    {
        $choices = new HotSpotChoiceCollection([new HotspotChoice('identifier1', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])), new HotspotChoice('identifier2', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]))]);
        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', new ObjectElement('http://my-data/data.png', 'image/png'), $choices);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must not exceed the number of choices available.");

        $graphicOrderInteraction->setMinChoices(3);
    }

    public function testSetMinChoicesWrongType()
    {
        $choices = new HotSpotChoiceCollection([new HotspotChoice('identifier1', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])), new HotspotChoice('identifier2', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]))]);
        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', new ObjectElement('http://my-data/data.png', 'image/png'), $choices);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must be a strictly negative or positive integer, 'string' given.");

        $graphicOrderInteraction->setMinChoices('3');
    }

    public function testSetMaxChoicesWrongType()
    {
        $choices = new HotSpotChoiceCollection([new HotspotChoice('identifier1', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])), new HotspotChoice('identifier2', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]))]);
        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', new ObjectElement('http://my-data/data.png', 'image/png'), $choices);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument must be a strictly negative or positive integer, 'string' given.");

        $graphicOrderInteraction->setMaxChoices('3');
    }

    public function testSetMaxDoesNotExceedsMinChoices()
    {
        $choices = new HotSpotChoiceCollection([new HotspotChoice('identifier1', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])), new HotspotChoice('identifier2', QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]))]);
        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', new ObjectElement('http://my-data/data.png', 'image/png'), $choices);
        $graphicOrderInteraction->setMinChoices(2);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument must be greater than or equal to the 'minChoices' attribute.");

        $graphicOrderInteraction->setMaxChoices(1);
    }
}
