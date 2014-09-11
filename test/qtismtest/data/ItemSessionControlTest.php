<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\ItemSessionControl;

class ItemSessionControlTest extends QtiSmTestCase {
	
    public function testIsDefault() {
        $itemSessionControl = new ItemSessionControl();
        $this->assertTrue($itemSessionControl->isDefault());
        
        $itemSessionControl->setMaxAttempts(0);
        $this->assertFalse($itemSessionControl->isDefault());
    }
}