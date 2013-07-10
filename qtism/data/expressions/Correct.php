<?php

namespace qtism\data\expressions;

use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * This expression looks up the declaration of a response variable and returns the associated 
 * correctResponse or NULL if no correct value was declared. When used in outcomes processing 
 * item identifier prefixing (see variable) may be used to obtain the correct response from 
 * an individual item.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Correct extends Expression {
	
	/**
	 * The identifier of the response variable you want the correct value.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * Create a new instance of Correct.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function __construct($identifier) {
		$this->setIdentifier($identifier);
	}
	
	/**
	 * Set the identifier of the response variable you want the correct value.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function setIdentifier($identifier) {
		if (Format::isIdentifier($identifier)) {
			$this->identifier = $identifier;
		}
		else {
			$msg = "'${identifier}' is not a valid QTI Identifier.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the identifier of the response variable you want the correct value.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	public function getQTIClassName() {
		return 'correct';
	}
}