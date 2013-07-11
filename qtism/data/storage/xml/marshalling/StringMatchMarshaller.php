<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\StringMatch;
use qtism\common\utils\Format;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of stringMatch QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StringMatchMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall a StringMatch object into a QTI stringMatch element.
	 * 
	 * @param QtiComponent The StringMatch object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI stringMatch element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		self::setDOMElementAttribute($element, 'caseSensitive', $component->isCaseSensitive());
		self::setDOMElementAttribute($element, 'substring', $component->mustSubstring());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI stringMatch operator element into an StringMatch object.
	 *
	 * @param DOMElement The stringMatch element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent An StringMatch object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($caseSensitive = static::getDOMElementAttributeAs($element, 'caseSensitive', 'boolean')) !== null) {

			$object = new StringMatch($children, $caseSensitive);
			
			if (($substring = static::getDOMElementAttributeAs($element, 'substring', 'boolean')) !== null) {
				$object->setSubstring($substring);
			}
			
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'caseSensitive' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}