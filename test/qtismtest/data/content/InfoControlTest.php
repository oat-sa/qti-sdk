<?php

namespace qtismtest\data\content;

use qtismtest\QtiSmTestCase;
use qtism\data\content\InfoControl;

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
