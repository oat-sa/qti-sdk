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

use qtism\data\TimeLimits;
use qtism\data\ItemSessionControl;

/**
 * Intrinsic runtime representation of the AssessmentTest QTI class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTest extends AbstractStructuralComponent {
    
    /**
     * The identifier of the AssessmentTest.
     * 
     * @var string
     */
    private $identifier;
    
    /**
     * The title of the AssessmentTest.
     * 
     * @var string
     */
    private $title;
    
    /**
     * Create a new AssessmentTest object.
     * 
     * @param string $identifier The identifier of the test.
     * @param string $title The title of the test.
     * @param ItemSessionControl $itemSessionControl The ItemSessionControl object ruling the test.
     * @param TimeLimits $timeLimits The TimeLimits object ruling the test.
     */
    public function __construct($identifier, $title, ItemSessionControl $itemSessionControl = null, TimeLimits $timeLimits = null) {
        parent::__construct($itemSessionControl, $timeLimits);
        $this->setIdentifier($identifier);
        $this->setTitle($title);
    }
    
    /**
     * Set the identifier of the test.
     * 
     * @param string $identifier
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }
    
    /**
     * Get the identifier of the test.
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }
    
    /**
     * Set the title of the test.
     * 
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    
    /**
     * Get the title of the test.
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
}