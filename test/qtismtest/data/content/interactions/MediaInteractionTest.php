<?php

declare(strict_types=1);

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\MediaInteraction;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class MediaInteractionTest
 */
class MediaInteractionTest extends QtiSmTestCase
{
    public function testCreateWrongAutostartType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'autostart' argument must be a boolean value, 'integer' given.");

        $mediaInteraction = new MediaInteraction('RESPONSE', 999, new ObjectElement('http://myobject.com/video.mpg', 'video/mpeg'));
    }

    public function testSetMinPlaysWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minPlays' argument must be a positive (>= 0) integer, 'boolean' given.");

        $mediaInteraction = new MediaInteraction('RESPONSE', true, new ObjectElement('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setMinPlays(true);
    }

    public function testSetMaxPlaysWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxPlays' argument must be a positive (>= 0) integer, 'boolean' given.");

        $mediaInteraction = new MediaInteraction('RESPONSE', true, new ObjectElement('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setMaxPlays(true);
    }

    public function testSetLoopWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'loop' argument must be a boolean value, 'integer' given.");

        $mediaInteraction = new MediaInteraction('RESPONSE', true, new ObjectElement('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setLoop(999);
    }

    public function testHasMinMAxPlays(): void
    {
        $mediaInteraction = new MediaInteraction('RESPONSE', true, new ObjectElement('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setMinPlays(1);
        $mediaInteraction->setMaxPlays(1);

        $this::assertTrue($mediaInteraction->hasMinPlays());
        $this::assertTrue($mediaInteraction->hasMaxPlays());
    }
}
