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
use qtism\data\TimeLimits;

/**
 * Interface for a Structural Component of an AssessmentTestSession e.g.
 * a TestPart, an AssessmentSection, ...
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface  StructuralComponent {

    /**
     * Set the TimeLimits object bound to the StructuralComponent.
     * 
     * @param TimeLimits $timeLimits
     */
    public function setTimeLimits(TimeLimits $timeLimits = null);
    
    /**
     * Get the TimeLimits object bound to the StructuralComponent.
     * 
     * @return TimeLimits
     */
    public function getTimeLimits();
    
    /**
     * Set the ItemSessionControl object bound to the StructuralComponent.
     * 
     * @param ItemSessionControl $itemSessionControl
     */
    public function setItemSessionControl(ItemSessionControl $itemSessionControl = null);
    
    /**
     * Get the ItemSessionControl object bound to the StructuralComponent.
     * 
     * @return ItemSessionControl
     */
    public function getItemSessionControl();
}