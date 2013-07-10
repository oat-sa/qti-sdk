<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\MapResponsePoint;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for mapResponsePoint.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapResponsePointMarshaller extends Marshaller {
	
	/**
	 * Marshall a MapResponsePoint object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A MapResponsePoint object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI mapResponsePoint element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A MapResponsePoint object.
	 * @throws UnmarshallingException If the mandatory attributes 'identifier' is missing.
	 */
	protected function unmarshall(DOMElement $element) {

		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
			$object = new MapResponsePoint($identifier);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'mapResponsePoint';
	}
}