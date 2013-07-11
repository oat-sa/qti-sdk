<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\Index;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of index QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IndexMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall an Index object into a QTI index element.
	 * 
	 * @param QtiComponent The Index object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI index element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		self::setDOMElementAttribute($element, 'n', $component->getN());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI index operator element into an Index object.
	 *
	 * @param DOMElement The index element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent An Index object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($n = static::getDOMElementAttributeAs($element, 'n')) !== null) {
			
			if (Format::isInteger($n)) {
				$n = intval($n);
			}
			
			$object = new Index($children, $n);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'n' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}