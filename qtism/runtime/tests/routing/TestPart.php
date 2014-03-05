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

use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;

/**
 * Intrinsic representation of a TestPart at runtime.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestPart extends AbstractStructuralComponent {
    
    /**
     * The identifier of the TestPart.
     * 
     * @var string
     */
    private $identifier;
    
    private $navigationMode;
    
    private $submissionMode;
    
    /**
     * Create a new TestPart object.
     * 
     * @param string $identifier The TestPart identifier.
     */
    public function __construct($identifier, ItemSessionControl $itemSessionControl = null, TimeLimits $timeLimits = null, $navigationMode = NavigationMode::LINEAR, $submissionMode = SubmissionMode::INDIVIDUAL) {
        parent::__construct($itemSessionControl, $timeLimits);
        $this->setIdentifier($identifier);
        $this->setNavigationMode($navigationMode);
        $this->setSubmissionMode($submissionMode);
    }
    
    /**
     * Get the identifier of the TestPart.
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }
    
    /**
     * Set the identifier of the TestPart.
     * 
     * @param string $identifier
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }
    
    public function setNavigationMode($navigationMode) {
        $this->navigationMode = $navigationMode;
    }
    
    public function getNavigationMode() {
        return $this->navigationMode;
    }
    
    public function setSubmissionMode($submissionMode) {
        $this->submissionMode = $submissionMode;
    }
    
    public function getSubmissionMode() {
        return $this->submissionMode;
    }
}