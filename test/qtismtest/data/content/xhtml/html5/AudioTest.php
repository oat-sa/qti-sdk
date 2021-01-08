<?php

namespace qtismtest\data\content\xhtml\html5;

use qtism\data\content\xhtml\html5\Audio;
use qtism\data\content\xhtml\html5\Preload;
use qtismtest\QtiSmTestCase;

class AudioTest extends QtiSmTestCase
{
    public function testGetQtiClassName()
    {
        $subject = new Audio();

        $this->assertEquals('audio', $subject->getQtiClassName());
    }
}
