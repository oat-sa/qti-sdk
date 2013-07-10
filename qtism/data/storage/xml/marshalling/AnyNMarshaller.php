<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\AnyN;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of anyN QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AnyNMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall an AnyN object into a QTI anyN element.
	 * 
	 * @param QtiComponent The AnyN object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI anyN element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQTIClassName());
		self::setDOMElementAttribute($element, 'min', $component->getMin());
		self::setDOMElementAttribute($element, 'max', $component->getMax());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI anyN operator element into an AnyN object.
	 *
	 * @param DOMElement The anyN element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent An AnyN object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($min = static::getDOMElementAttributeAs($element, 'min')) !== null) {
			
			if (Format::isInteger($min)) {
				$min = intval($min);
			}
			
			if (($max = static::getDOMElementAttributeAs($element, 'max')) !== null) {
				
				if (Format::isInteger($max)) {
					$max = intval($max);
				}
				
				$object = new AnyN($children, $min, $max);
				return $object;
			}
			else {
				$msg = "The mandatory attribute 'max' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'min' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}