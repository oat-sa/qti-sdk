<?php

namespace qtism\data;

use \ReflectionClass;
use \InvalidArgumentException;

/**
 * This extension of QtiComponentCollection can retrieve items it contains
 * by identifier.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class QtiIdentifiableCollection extends QtiComponentCollection {
	
	protected function checkType($value) {
		parent::checkType($value);
		
		if (!$value instanceof QtiIdentifiable) {
			$msg = "The QtiIdentifiable class only accepts to store QtiIdentifiable objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get a QtiComponent contained in the collection by its identifier.
	 * If no QtiComponent with $identifier is found in the collection, null is returned.
	 * 
	 * @param  $identifier The identifier of the QtiComponent to retrieve.
	 * @return QtiComponent A QtiComponent object or null if not found.
	 */
	public function getByIdentifier($identifier) {
		foreach ($this as $component) {
			if ($component->getIdentifier() === $identifier) {
				return $component;
			}
		}
		
		return null;
	}
	
}