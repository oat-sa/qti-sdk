<?php

namespace qtism\data\expressions;

use qtism\common\utils\Format;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * This expression looks up the value of a response variable and then transforms it using the 
 * associated mapping, which must have been declared. The result is a single float. If the 
 * response variable has single cardinality then the value returned is simply the mapped 
 * target value from the map. If the response variable has multiple or ordered cardinality 
 * then the value returned is the sum of the mapped target values. This expression cannot 
 * be applied to variables of record cardinality.
 * 
 * For example, if a mapping associates the identifiers {A,B,C,D} with the values {0,1,0.5,0} 
 * respectively then mapResponse will map the single value 'C' to the numeric value 0.5 and 
 * the set of values {C,B} to the value 1.5.
 * 
 * If a container contains multiple instances of the same value then that value is counted 
 * once only. To continue the example above {B,B,C} would still map to 1.5 and not 2.5.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapResponse extends Expression {
	
	/**
	 * The QTI identifier of the associated mapping.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * Create a new instance of MapResponse.
	 * 
	 * @param string $identifier The QTI Identifier of the associated mapping.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function __construct($identifier) {
		$this->setIdentifier($identifier);
	}
	
	/**
	 * Set the identifier of the associated mapping.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function setIdentifier($identifier) {
		if (Format::isIdentifier($identifier, false)) {
			$this->identifier = $identifier;
		}
		else {
			$msg = "${identifier} is not a valid QTI Identifier.";
			throw new InvalidArgumentException($identifier);
		}
	}
	
	/**
	 * Get the identifier of the associated mapping.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	public function getQtiClassName() {
		return 'mapResponse';
	}
}