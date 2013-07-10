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
	abstract public function getQTIClassName();
	
	/**
	 * Get the direct child components of this one.
	 * 
	 * @return QtiComponentCollection A collection of QtiComponent objects.
	 */
	abstract public function getComponents();
}