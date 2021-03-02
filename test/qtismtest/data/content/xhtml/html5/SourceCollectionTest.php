<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\content\xhtml\html5\SourceCollection;
use qtismtest\QtiSmTestCase;

class SourceCollectionTest extends QtiSmTestCase
{
    public function testCheckWithValidObject(): void
    {
        $src = 'http://example.com/';
        $type = 'video/webm';

        $source = new Source($src, $type);
        $subject = new SourceCollection([$source]);
            
        self::assertSame([$source], $subject->getArrayCopy());
    }

    public function testCheckWithInvalidObject(): void
    {
        $nonSource = 12;
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("SourceCollection only accepts to store Source objects, '" . gettype($nonSource) . "' given.");

        (new SourceCollection([$nonSource]));
    }
}
