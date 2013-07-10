<?php

namespace qtism\data;

use \ReflectionClass;

/**
 * This extension of QtiComponentCollection can retrieve items it contains
 * by identifier.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class QtiIdentifiableCollection extends QtiComponentCollection {
	
	/**
	 * Get a QtiComponent contained in the collection by its identifier.
	 * If no QtiComponent with $identifier is found in the collection, null is returned.
	 * 
	 * @param  $identifier The identifier of the QtiComponent to retrieve.
	 * @return QtiComponent A QtiComponent object or null if not found.
	 */
	public function getByIdentifier($identifier) {
		foreach ($this as $component) {
			$reflection = new ReflectionClass($component);
			if ($reflection->hasMethod('getIdentifier') && $component->getIdentifier() === $identifier) {
				return $component;
			}
		}
		
		return null;
	}
	
}