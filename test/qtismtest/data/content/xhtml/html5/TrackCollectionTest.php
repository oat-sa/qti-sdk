<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\xhtml\html5\TrackCollection;
use qtismtest\QtiSmTestCase;

class TrackCollectionTest extends QtiSmTestCase
{
    public function testCheckWithValidObject(): void
    {
        $src = 'http://example.com/';

        $track = new Track($src);
        $subject = new TrackCollection([$track]);

        self::assertSame([$track], $subject->getArrayCopy());
    }

    public function testCheckWithInvalidObject(): void
    {
        $nonSource = 12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("TrackCollection only accepts to store Track objects, '" . gettype($nonSource) . "' given.");

        (new TrackCollection([$nonSource]));
    }
}
