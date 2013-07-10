<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\rules\Selection;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for selection.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SelectionMarshaller extends Marshaller {
	
	/**
	 * Marshall a Selection object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A Selection object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'select', $component->getSelect());
		self::setDOMElementAttribute($element, 'withReplacement', $component->isWithReplacement());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI Selection object.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A Selection object.
	 * @throws UnmarshallingException If the mandatory 'select' attribute is missing from $element.
	 */
	protected function unmarshall(DOMElement $element) {
		
		// select is a mandatory value, retrieve it first.
		if (($value = static::getDOMElementAttributeAs($element, 'select', 'integer')) !== null) {
			$object = new Selection($value);
			
			if (($value = static::getDOMElementAttributeAs($element, 'withReplacement', 'boolean')) !== null) {
				$object->setWithReplacement($value);
			}
		}
		else {
			$msg = "The mandatory attribute 'select' is missing.";
			throw new UnmarshallingException($msg, $element);
		}
		
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'selection';
	}
}