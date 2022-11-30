<?php

declare(strict_types=1);

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\AssociateInteraction;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class AssociateInteractionTest
 */
class AssociateInteractionTest extends QtiSmTestCase
{
    public function testCreateNoAssociableChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An AssociateInteraction object must be composed of at lease one SimpleAssociableChoice object, none given.');
        $associateInteraction = new AssociateInteraction('RESPONSE', new SimpleAssociableChoiceCollection());
    }

    public function testSetMinAssociations(): void
    {
        $associateInteraction = new AssociateInteraction('RESPONSE', new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('identifier', 1)]));
        $associateInteraction->setMinAssociations(1);
        $this::assertEquals(1, $associateInteraction->getMinAssociations());
        $this::assertTrue($associateInteraction->hasMinAssociations());
    }

    public function testSetMinAssociationsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minAssociations' argument must be a positive (>= 0) integer, 'boolean' given.");

        $associateInteraction = new AssociateInteraction('RESPONSE', new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('identifier', 1)]));
        $associateInteraction->setMinAssociations(true);
    }

    public function testSetMinAssociationsIllogicValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minAssociations' argument must be less than or equal to the limit imposed by 'maxAssociations'.");

        $associateInteraction = new AssociateInteraction('RESPONSE', new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('identifier', 1)]));
        $associateInteraction->setMaxAssociations(1);
        $associateInteraction->setMinAssociations(3);
    }

    public function testSetMaxAssociationsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxAssociations' argument must be a positive (>= 0) integer, 'boolean' given.");

        $associateInteraction = new AssociateInteraction('RESPONSE', new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('identifier', 1)]));
        $associateInteraction->setMaxAssociations(true);
    }

    public function testSetShuffleWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'shuffle' argument must be a boolean value, 'string' given.");

        $associateInteraction = new AssociateInteraction('RESPONSE', new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('identifier', 1)]));
        $associateInteraction->setShuffle('true');
    }
}
