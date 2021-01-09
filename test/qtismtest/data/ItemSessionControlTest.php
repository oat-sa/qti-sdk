<?php

namespace qtismtest\data;

use qtism\data\ItemSessionControl;
use qtismtest\QtiSmTestCase;

/**
 * Class ItemSessionControlTest
 */
class ItemSessionControlTest extends QtiSmTestCase
{
    public function testIsDefault()
    {
        $itemSessionControl = new ItemSessionControl();
        $this::assertTrue($itemSessionControl->isDefault());

        $itemSessionControl->setMaxAttempts(0);
        $this::assertFalse($itemSessionControl->isDefault());
    }
}
