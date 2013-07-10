<?php

namespace qtism\data\expressions;

use qtism\common\utils\Format;
use qtism\common\collections\IdentifierCollection;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * This class defines the concept of a sub-set of the items selected in an assessmentTest.
 * The attributes define criteria that must be matched by all members of the sub-set. 
 * It is used to control a number of expressions in outcomeProcessing for returning 
 * information about the test as a whole, or abitrary subsets of it.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ItemSubset extends Expression {
	
	/**
	 * From IMS QTI:
	 * 
	 * If specified, only variables from items in the assessmentSection with matching 
	 * identifier are matched. Items in sub-sections are included in this definition.
	 * 
	 * @var string
	 */
	private $sectionIdentifier = '';
	
	/**
	 * From IMS QTI:
	 * 
	 * If specified, only variables from items with a matching category are included.
	 * 
	 * @var IdentifierCollection
	 */
	private $includeCategories = null;
	
	/**
	 * From IMS QTI:
	 * 
	 * If specified, only variables from items with no matching category are included.
	 * 
	 * @var IdentifierCollection
	 */
	private $excludeCategories = null;
	
	/**
	 * Create a new instance of ItemSubset.
	 * 
	 */
	public function __construct() {
		$this->setIncludeCategories(new IdentifierCollection());
		$this->setExcludeCategories(new IdentifierCollection());
	}
	
	/**
	 * Set the assessment section identifier to match.
	 * 
	 * @param string $sectionIdentifier A QTI Identifier. 
	 * @throws InvalidArgumentException If $sectionIdentifier is not a valid QTI Identifier.
	 */
	public function setSectionIdentifier($sectionIdentifier) {
		if (Format::isIdentifier($sectionIdentifier) || empty($sectionIdentifier)) {
			$this->sectionIdentifier = $sectionIdentifier;
		}
		else {
			$msg = "'${sectionIndentifier}' is not a valid QTI Identifier.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the assessment section identifier to match.
	 * 
	 * @return string
	 */
	public function getSectionIdentifier() {
		return $this->sectionIdentifier;
	}
	
	/**
	 * Set the matching categories.
	 * 
	 * @param IdentifierCollection $includeCategories A collection of QTI Identifiers.
	 */
	public function setIncludeCategories(IdentifierCollection $includeCategories) {
		$this->includeCategories = $includeCategories;
	}
	
	/**
	 * Get the matching categories.
	 * 
	 * @return IdentifierCollection
	 */
	public function getIncludeCategories() {
		return $this->includeCategories;
	}
	
	/**
	 * Set the categories that must not be matched.
	 * 
	 * @param IdentifierCollection $excludeCategories A collection of QTI Identifiers.
	 */
	public function setExcludeCategories(IdentifierCollection $excludeCategories) {
		$this->excludeCategories = $excludeCategories;
	}
	
	/**
	 * Get the categories that must not be matched.
	 * 
	 * @return IdentifierCollection
	 */
	public function getExcludeCategories() {
		return $this->excludeCategories;
	}
	
	public function getQTIClassName() {
		return 'itemSubset';
	}
}