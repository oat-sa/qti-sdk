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

use qtism\data\content\RubricBlockRefCollection;
use qtism\data\content\RubricBlockRef;
use qtism\data\TimeLimits;

/**
 * Intrinsic runtime representation of the QTI AssessmentSection class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentSection extends AbstractStructuralComponent {
    
    /**
     * The identifier of the AssessmentSection.
     * 
     * @var string
     */
    private $identifier;
    
    /**
     * The title of the AssessmentSection.
     * 
     * @var string
     */
    private $title;
    
    /**
     * Whether or not the AssessmentSection is considered to be visible
     * by the candidate.
     * 
     * @var boolean
     */
    private $visible;
    
    /**
     * A collection of RubricBlockRef objects.
     * 
     * @var RubricBlockRefCollection
     */
    private $rubricBlockRefs;
    
    /**
     * Create a new instance of AssessmentSection.
     * 
     * @param string $identifier An identifier.
     * @param string $title A title.
     * @param boolean $visible Whether or not the section is considered to be visible to the candidate.
     * @param ItemSessionControl $itemSessionControl The ItemSessionControl object ruling the section.
     * @param TimeLimits $timeLimits The TimeLimits object ruling the section.
     */
    public function __construct($identifier, $title, $visible = true, ItemSessionControl $itemSessionControl = null, TimeLimits $timeLimits = null) {
        parent::__construct($itemSessionControl, $timeLimits);
        $this->setIdentifier($identifier);
        $this->setTitle($title);
        $this->setVisible($visible);
        $this->setRubricBlockRefs(new RubricBlockRefCollection());
    }
    
    /**
     * Set the identifier of the AssessmentSection.
     * 
     * @param string $identifier
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }
    
    /**
     * Get the identifier of the AssessmentSection.
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }
    
    /**
     * Set the title of the AssessmentSection.
     * 
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    
    /**
     * Get the title of the AssessmentSection.
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Specifies whether or not the section is considered to be
     * visible to the candidate.
     * 
     * @param boolean $visible
     */
    public function setVisible($visible) {
        $this->visible = $visible;
    }
    
    /**
     * Whether or not the section is considered to be visible
     * to the candidate.
     * 
     * @return boolean
     */
    public function isVisible() {
        return $this->visible;
    }
    
    /**
     * Set the collection of RubricBlockRef objects related to the section.
     * 
     * @param RubricBlockRefCollection $rubricBlockRefs
     */
    protected function setRubricBlockRefs(RubricBlockRefCollection $rubricBlockRefs) {
        $this->rubricBlockRefs = $rubricBlockRefs;
    }
    
    /**
     * Add a RubricBlockRef object to the section.
     * 
     * @param RubricBlockRef $rubricBlockRef
     */
    public function addRubricBlockRef(RubricBlockRef $rubricBlockRef) {
        $this->rubricBlockRefs[] = $rubricBlockRef;
    }
    
    public function addRubricBlockRefs(RubricBlockRefCollection $rubricBlockRefs) {
        $this->rubricBlockRefs->merge($rubricBlockRefs);
    }
    
    /**
     * Get the collection of RubricBlock objects composing the section.
     * 
     * @return RubricBlockRefCollection
     */
    public function getRubricBlockRefs() {
        return $this->rubricBlockRefs;
    }
}