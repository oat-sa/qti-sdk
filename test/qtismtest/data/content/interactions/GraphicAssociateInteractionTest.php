<?php

namespace qtismtest\data\content\interactions;

use qtism\common\datatypes\QtiShape;
use qtism\common\datatypes\QtiCoords;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\content\interactions\AssociableHotspotCollection;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\GraphicAssociateInteraction;

class GraphicAssociateInteractionTest extends QtiSmTestCase
{
    public function testCreateNotEnoughAssociableHotspots()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "A GraphicAssociateInteraction must be composed of at least 1 AssociableHotspot object, none given."
        );
        
        new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection()
        );
    }
    
    public function testSetMaxAssociationsWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'maxAssociations' argument must be a positive (>= 0) integer, 'boolean' given."
        );
        
        $interaction = new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection([
                new AssociableHotspot('hotspot1', 1, QtiShape::RECT, new QtiCoords(QtiShape::RECT, array(0, 0, 1, 1)))
            ])
        );
        
        $interaction->setMaxAssociations(true);
    }
    
    public function testSetMinAssociationsWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'minAssociations' argument must be a positive (>= 0) integer, 'boolean'."
        );
        
        $interaction = new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection([
                new AssociableHotspot('hotspot1', 1, QtiShape::RECT, new QtiCoords(QtiShape::RECT, array(0, 0, 1, 1)))
            ])
        );
        
        $interaction->setMaxAssociations(1);
        $interaction->setMinAssociations(true);
    }
    
    public function testSetMinAssociationsInvalidValue()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'minAssociations' argument must be less than or equal to the limit imposed by 'maxAssociations'."
        );
        
        $interaction = new GraphicAssociateInteraction(
            'RESPONSE',
            new ObjectElement('image.png', 'image/png'),
            new AssociableHotspotCollection([
                new AssociableHotspot('hotspot1', 1, QtiShape::RECT, new QtiCoords(QtiShape::RECT, array(0, 0, 1, 1)))
            ])
        );
        
        $interaction->setMaxAssociations(1);
        $interaction->setMinAssociations(2);
    }
}
