<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\RoundTo;
use qtism\data\expressions\operators\RoundingMode;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of RoundTo QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RoundToMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall a RoundTo object into a QTI roundTo element.
	 * 
	 * @param QtiComponent The RoundTo object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI roundTo element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'figures', $component->getFigures());
		self::setDOMElementAttribute($element, 'roundingMode', RoundingMode::getNameByConstant($component->getRoundingMode()));
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI roundTo operator element into a RoundTo object.
	 *
	 * @param DOMElement The roundTo element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent A RoundTo object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($figures = static::getDOMElementAttributeAs($element, 'figures', 'string')) !== null) {
				
			if (!Format::isVariableRef($figures)) {
				$figures = intval($figures);
			}
				
			$object = new RoundTo($children, $figures);
				
			if (($roundingMode = static::getDOMElementAttributeAs($element, 'roundingMode')) !== null) {
				$object->setRoundingMode(RoundingMode::getConstantByName($roundingMode));
			}
				
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'figures' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}