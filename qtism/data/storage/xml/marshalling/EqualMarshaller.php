<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of equal QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class EqualMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall an Equal object into a QTI equal element.
	 * 
	 * @param QtiComponent The Equal object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI equal element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		self::setDOMElementAttribute($element, 'toleranceMode', ToleranceMode::getNameByConstant($component->getToleranceMode()));
		
		$tolerance = $component->getTolerance();
		if (!empty($tolerance)) {
			self::setDOMElementAttribute($element, 'tolerance', implode("\x20", $tolerance));
		}
		
		self::setDOMElementAttribute($element, 'includeLowerBound', $component->doesIncludeLowerBound());
		self::setDOMElementAttribute($element, 'includeUpperBound', $component->doesIncludeUpperBound());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI equal operator element into an Equal object.
	 *
	 * @param DOMElement The equal element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent An Equal object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		$object = new Equal($children);
		
		if (($toleranceMode = static::getDOMElementAttributeAs($element, 'toleranceMode')) !== null) {
			$toleranceMode = ToleranceMode::getConstantByName($toleranceMode);
			$object->setToleranceMode($toleranceMode);
		}
		
		if (($tolerance = static::getDOMElementAttributeAs($element, 'tolerance')) !== null) {
			$tolerance = explode("\x20", $tolerance);
			
			if (count($tolerance) < 1) {
				$msg = "No 'tolerance' could be extracted from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
			else if (count($tolerance) > 2) {
				$msg = "'tolerance' attribute not correctly formatted in element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
			else {
				$finalTolerance = array();
				foreach ($tolerance as $t) {
					$finalTolerance[] = (Format::isFloat($t)) ? floatval($t) : $t;
				}
				
				$object->setTolerance($finalTolerance);
			}
		}
		
		if (($includeLowerBound = static::getDOMElementAttributeAs($element, 'includeLowerBound', 'boolean')) !== null) {
			$object->setIncludeLowerBound($includeLowerBound);
		}
		
		if (($includeUpperBound = static::getDOMElementAttributeAs($element, 'includeUpperBound', 'boolean')) !== null) {
			$object->setIncludeUpperBound($includeUpperBound);
		}
		
		return $object;
	}
}