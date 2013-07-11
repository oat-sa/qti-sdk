<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\StatsOperator;
use qtism\data\expressions\operators\Statistics;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of StatOperators QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StatsOperatorMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall a StatsOperator object into a QTI statsOperator element.
	 * 
	 * @param QtiComponent The StatsOperator object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI statsOperator element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'name', Statistics::getNameByConstant($component->getName()));
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI statsOperator operator element into a StatsOperator object.
	 *
	 * @param DOMElement The statsOperator element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent A StatsOperator object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($name = static::getDOMElementAttributeAs($element, 'name')) !== null) {
			
			$object = new StatsOperator($children, Statistics::getConstantByName($name));
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'name' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}