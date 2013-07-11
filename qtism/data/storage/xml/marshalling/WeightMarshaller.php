<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\Weight;
use qtism\common\utils\Format;
use \DOMElement;
use \InvalidArgumentException;

/**
 * Marshalling/Unmarshalling implementation for weight.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class WeightMarshaller extends Marshaller {
	
	/**
	 * Marshall a Weight object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A Weight object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		self::setDOMElementAttribute($element, 'value', $component->getValue());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI weight element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A Weight object.
	 * @throws UnmarshallingException If the mandatory attributes 'identifier' or 'value' are missing from $element but also if 'value' cannot be converted to a float value or 'identifier' is not a valid QTI Identifier.
	 */
	protected function unmarshall(DOMElement $element) {
		
		// identifier is a mandatory value.
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
			if (($value = static::getDOMElementAttributeAs($element, 'value', 'string')) !== null) {
				if (Format::isFloat($value)) {
					try {
						$object = new Weight($identifier, floatval($value));
						return $object;
					}
					catch (InvalidArgumentException $e) {
						$msg = "The value of 'identifier' from element '" . $element->nodeName . "' is not a valid QTI Identifier.";
						throw new UnmarshallingException($msg, $element, $e);
					}
				}
				else {
					$msg = "The value of attribute 'value' from element '" . $element->nodeName . "' cannot be converted into a float.";
					throw new UnmarshallingException($msg, $element);
				}
			}
			else {
				$msg = "The mandatory attribute 'value' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'weight';
	}
}