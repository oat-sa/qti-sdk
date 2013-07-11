<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\PatternMatch;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of patternMatch QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PatternMatchMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall a PatternMatch object into a QTI patternMatch element.
	 * 
	 * @param QtiComponent The PatternMatch object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI patternMatch element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		self::setDOMElementAttribute($element, 'pattern', $component->getPattern());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI patternMatch operator element into an PatternMatch object.
	 *
	 * @param DOMElement The patternMatch element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent A PatternMatch object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($pattern = static::getDOMElementAttributeAs($element, 'pattern')) !== null) {
			
			$object = new PatternMatch($children, $pattern);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'pattern' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}