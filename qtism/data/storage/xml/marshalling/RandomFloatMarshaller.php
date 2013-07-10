<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\RandomFloat;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for randomFloat.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RandomFloatMarshaller extends Marshaller {
	
	/**
	 * Marshall a RandomFloat object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A RandomFloat object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'min', $component->getMin());
		self::setDOMElementAttribute($element, 'max', $component->getMax());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI randomFloat element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A RandomFloat object.
	 * @throws UnmarshallingException If the mandatory attributes min or max ar missing.
	 */
	protected function unmarshall(DOMElement $element) {
		
		// max attribute is mandatory.
		if (($max = static::getDOMElementAttributeAs($element, 'max')) !== null) {
			$max = (Format::isVariableRef($max)) ? $max : floatval($max);
			
			$object = new RandomFloat(0.0, $max);
			
			if (($min = static::getDOMElementAttributeAs($element, 'min')) !== null) {
				$min = (Format::isVariableRef($min)) ? $min : floatval($min);
				$object->setMin($min);
			}
			
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'max' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'randomFloat';
	}
}