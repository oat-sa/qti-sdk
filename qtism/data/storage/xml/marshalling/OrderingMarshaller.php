<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\rules\Ordering;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for ordering.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OrderingMarshaller extends Marshaller {
	
	/**
	 * Marshall an Ordering object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An Ordering object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'shuffle', $component->getShuffle());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI Ordering element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent An Ordering object.
	 */
	protected function unmarshall(DOMElement $element) {
		$object = new Ordering();
		
		if (($value = static::getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
			$object->setShuffle($value);
		}
		
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'ordering';
	}
}