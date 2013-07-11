<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\MathOperator;
use qtism\data\expressions\operators\MathFunctions;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of mathOperator QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MathOperatorMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall a MathOperator object into a QTI mathOperator element.
	 * 
	 * @param QtiComponent The MathOperator object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI mathOperator element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'name', MathFunctions::getNameByConstant($component->getName()));
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI mathOperator operator element into a MathsOperator object.
	 *
	 * @param DOMElement The mathOperator element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent A MathOperator object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($name = static::getDOMElementAttributeAs($element, 'name')) !== null) {
			
			$object = new MathOperator($children, MathFunctions::getConstantByName($name));
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'name' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}