<?php

namespace qtism\data;

/**
 * Any class which corresponds to a QTI component
 * must implement this class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class QtiComponent {
	
	/**
	 * Returns the QTI class name as per QTI 2.1 specification.
	 * 
	 * @return string A QTI class name.
	 */
	abstract public function getQtiClassName();
	
	/**
	 * Get the direct child components of this one.
	 * 
	 * @return QtiComponentCollection A collection of QtiComponent objects.
	 */
	abstract public function getComponents();
	
	/**
	 * Get a QtiComponentIterator object which allows you to iterate
	 * on all QtiComponent objects hold by this one.
	 * 
	 * @return QtiComponentIterator A QtiComponentIterator object.
	 */
	public function getIterator() {
		return new QtiComponentIterator($this);
	}
	
	/**
	 * Get a QtiComponent object which is contained by this on the basis
	 * of a given $identifier.
	 * 
	 * @return QtiComponent|null A QtiComponent object or null if not found.
	 */
	public function getComponentByIdentifier($identifier) {
		$iterator = $this->getIterator();
		
		foreach ($iterator as $component) {
			if ($component instanceof QtiIdentifiable && $component->getIdentifier() === $identifier) {
				return $component;
			}
		}
		
		return null;
	}
}