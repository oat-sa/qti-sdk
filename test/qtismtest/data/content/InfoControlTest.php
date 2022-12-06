<?php

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\InfoControl;
use qtismtest\QtiSmTestCase;

/**
 * Class InfoControlTest
 */
class InfoControlTest extends QtiSmTestCase
{
    public function testSetTitleWrongType(): void
    {
        $infoControl = new InfoControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'title' argument must be a string, 'integer' given.");

        $infoControl->setTitle(999);
    }
}
