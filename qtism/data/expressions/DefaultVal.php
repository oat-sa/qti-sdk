<?php

namespace qtism\data\expressions;

use \InvalidArgumentException;

/**
 * Warning: This class is named DefaultVal instead of Default (name in QTI) because
 * of the reserved word 'default' of PHP.
 * 
 * From IMS QTI:
 * 
 * This expression looks up the declaration of an itemVariable and returns the associated 
 * defaultValue or NULL if no default value was declared. When used in outcomes processing 
 * item identifier prefixing (see variable) may be used to obtain the default value from an 
 * individual item.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @link http://www.php.net/manual/en/reserved.keywords.php
 */
class DefaultVal extends Expression {
	
	/**
	 * The QTI Identifier of the variable you want the default value.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * Create a new instance of DefaultValue.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function __construct($identifier) {
		$this->setIdentifier($identifier);
	}
	
	/**
	 * Set the identifier of the variable you want the default value.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}
	
	/**
	 * Get the identifier of the variable you want the default value.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	public function getQTIClassName() {
		return 'default';
	}
}