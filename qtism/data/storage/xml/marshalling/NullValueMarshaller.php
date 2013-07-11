<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\NullValue;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for null.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NullValueMarshaller extends Marshaller {
	
	/**
	 * Marshall a NullValue object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A NullValue object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI null element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A NullValue object.
	 */
	protected function unmarshall(DOMElement $element) {
		$object = new NullValue();
		return $object;
	}
	
	public function getExpectedQtiClassName() {
		return 'null';
	}
}