<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */
namespace qtism\runtime\tests\routing;

use qtism\data\ItemSessionControl;

/**
 * Represent the composition of an ItemSessionControl object
 * and its related owner.
 * 
 * @author <jerome@taotesting.com>
 *
 */
class StepItemSessionControl extends OwnedObject {
    
    /**
     * The ItemSessionControl object.
     * 
     * @var ItemSessionControl
     */
    private $itemSessionControl;
    
    /**
     * Create a new StepItemSessionControl object.
     * 
     * @param StructuralTestComponent $owner The owner object.
     * @param ItemSessionControl $itemSessionControl The composite ItemSessionControl object.
     */
    public function __construct(StructuralComponent $owner, ItemSessionControl $itemSessionControl) {
        parent::__construct($owner);
        $this->setItemSessionControl($itemSessionControl);
    }
    
    /**
     * Set the ItemSessionControl object.
     * 
     * @param ItemSessionControl $itemSessionControl
     */
    public function setItemSessionControl(ItemSessionControl $itemSessionControl) {
        $this->itemSessionControl = $itemSessionControl;
    }
    
    /**
     * Get the ItemSessionControl object.
     * 
     * @return ItemSessionControl
     */
    public function getItemSessionControl() {
        return $this->itemSessionControl;
    }
}