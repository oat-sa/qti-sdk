<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\Repeat;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of repeat QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RepeatMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall a Repeat object into a QTI repeat element.
	 * 
	 * @param QtiComponent The Repeat object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI repeat element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQTIClassName());
		self::setDOMElementAttribute($element, 'numberRepeats', $component->getNumberRepeats());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI repeat operator element into a Repeat object.
	 *
	 * @param DOMElement The repeat element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent A Repeat object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($numberRepeats = static::getDOMElementAttributeAs($element, 'numberRepeats')) !== null) {
			
			if (Format::isInteger($numberRepeats)) {
				$numberRepeats = intval($numberRepeats);
			}
			
			$object = new Repeat($children, $numberRepeats);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'numberRepeats' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}