<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\MapResponse;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for mapResponse.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapResponseMarshaller extends Marshaller {
	
	/**
	 * Marshall a MapResponse object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A MapResponse object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI mapResponse element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A MapResponse object.
	 * @throws UnmarshallingException If the mandatory attributes 'identifier' is missing.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
			$object = new MapResponse($identifier);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'mapResponse';
	}
}