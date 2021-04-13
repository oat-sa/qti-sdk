<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\content\interactions\AssociableHotspotCollection;
use qtism\data\content\interactions\GraphicAssociateInteraction;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class GraphicAssociateInteractionTest
 */
class GraphicAssociateInteractionTest extends QtiSmTestCase
{
    public function testCreateNotEnoughAssociableHotspots()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A GraphicAssociateInteraction must be composed of at least 1 AssociableHotspot object, none given.');

        new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection()
        );
    }

    public function testSetMaxAssociationsWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxAssociations' argument must be a positive (>= 0) integer, 'boolean' given.");

        $interaction = new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection([
                new AssociableHotspot('hotspot1', 1, QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])),
            ])
        );

        $interaction->setMaxAssociations(true);
    }

    public function testSetMinAssociationsWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minAssociations' argument must be a positive (>= 0) integer, 'boolean'.");

        $interaction = new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection([
                new AssociableHotspot('hotspot1', 1, QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])),
            ])
        );

        $interaction->setMaxAssociations(1);
        $interaction->setMinAssociations(true);
    }

    public function testSetMinAssociationsInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minAssociations' argument must be less than or equal to the limit imposed by 'maxAssociations'.");

        $interaction = new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection([
                new AssociableHotspot('hotspot1', 1, QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1])),
            ])
        );

        $interaction->setMaxAssociations(1);
        $interaction->setMinAssociations(2);
    }
}
