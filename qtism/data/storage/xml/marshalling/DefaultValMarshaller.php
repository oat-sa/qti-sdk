<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\DefaultVal;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for default.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DefaultValMarshaller extends Marshaller {
	
	/**
	 * Marshall a DefaultVal object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A DefaultVal object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI default element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A DefaultVal object.
	 * @throws UnmarshallingException If the mandatory attributes 'identifier' is missing.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
			$object = new DefaultVal($identifier);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'default';
	}
}