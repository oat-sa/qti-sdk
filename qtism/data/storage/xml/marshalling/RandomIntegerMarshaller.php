<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\RandomInteger;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for randomInteger.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RandomIntegerMarshaller extends Marshaller {
	
	/**
	 * Marshall a RandomInteger object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A RandomInteger object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'min', $component->getMin());
		self::setDOMElementAttribute($element, 'max', $component->getMax());
		
		if ($component->getStep() !== 1) { // default value of the step attribute is 1.
			self::setDOMElementAttribute($element, 'step', $component->getStep());
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI randomInteger element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A RandomInteger object.
	 * @throws UnmarshallingException If the mandatory attributes 'min' or 'max' are missing from $element.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($max = static::getDOMElementAttributeAs($element, 'max', 'string')) !== null) {
			$max = (Format::isVariableRef($max)) ? $max : intval($max);
			$object = new RandomInteger(0, $max);
			
			if (($step = static::getDOMElementAttributeAs($element, 'step')) !== null) {
				$object->setStep(abs(intval($step)));
			}
			
			if (($min = static::getDOMElementAttributeAs($element, 'min')) !== null) {
				$min = (Format::isVariableRef($min)) ? $min : intval($min);
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
		return 'randomInteger';
	}
}