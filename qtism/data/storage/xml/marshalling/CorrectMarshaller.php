<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\Correct;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for correct.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class CorrectMarshaller extends Marshaller {
	
	/**
	 * Marshall a Correct object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A Correct object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI correct element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A Correct object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
			$object = new Correct($identifier);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'correct';
	}
}