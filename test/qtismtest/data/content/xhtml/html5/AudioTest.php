<?php

namespace qtismtest\data\content\xhtml\html5;

use qtism\data\content\xhtml\html5\Audio;
use qtismtest\QtiSmTestCase;

class AudioTest extends QtiSmTestCase
{
    public function testGetQtiClassName(): void
    {
        $subject = new Audio();

        self::assertEquals('audio', $subject->getQtiClassName());
    }
}
