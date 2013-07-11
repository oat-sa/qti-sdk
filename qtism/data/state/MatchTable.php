<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * A matchTable transforms a source integer by finding the first matchTableEntry with 
 * an exact match to the source.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MatchTable extends LookupTable {
	
	/**
	 * A collection of MatchTableEntry objects.
	 * 
	 * @var MatchTableEntryCollection
	 */
	private $matchTableEntries;
	
	/**
	 * Create a new instance of MatchTable.
	 * 
	 * @param MatchTableEntryCollection $matchTableEntries A collection of MatchTableEntry objects.
	 * @param mixed $defaultValue The default oucome value to be used when no matching table entry is found.
	 * @throws InvalidArgumentException If $matchTableEntries is an empty collection.
	 */
	public function __construct(MatchTableEntryCollection $matchTableEntries, $defaultValue = null) {
		parent::__construct($defaultValue);
		$this->setMatchTableEntries($matchTableEntries);
	}
	
	/**
	 * Get the collection of MatchTableEntry objects.
	 * 
	 * @return MatchTableEntryCollection A collection of MatchTableEntry objects.
	 */
	public function getMatchTableEntries() {
		return $this->matchTableEntries;
	}
	
	/**
	 * Set the collection of MatchTableEntry objects.
	 * 
	 * @param MatchTableEntryCollection $matchTableEntries A collection of MatchTableEntry objects.
	 * @throws InvalidArgumentException If $matchTableEntries is an empty collection.
	 */
	public function setMatchTableEntries(MatchTableEntryCollection $matchTableEntries) {
		if (count($matchTableEntries) > 0) {
			$this->matchTableEntries = $matchTableEntries;
		}
		else {
			$msg = "A MatchTable object must contain at least one MatchTableEntry object.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'matchTable';
	}
	
	public function getComponents() {
		$comp = array_merge(parent::getComponents()->getArrayCopy(),
							$this->getMatchTableEntries()->getArrayCopy());
		return new QtiComponentCollection($comp);
	}
}