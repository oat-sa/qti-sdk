<?php

namespace qtismtest\data\content;

use qtism\data\content\InfoControl;
use qtismtest\QtiSmTestCase;

class InfoControlTest extends QtiSmTestCase
{
    public function testSetTitleWrongType()
    {
        $infoControl = new InfoControl();

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'title' argument must be a string, 'integer' given."
        );

        $infoControl->setTitle(999);
    }
}
