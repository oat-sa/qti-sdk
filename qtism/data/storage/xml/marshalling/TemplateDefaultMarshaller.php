<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\TemplateDefault;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for templateDefault.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TemplateDefaultMarshaller extends Marshaller {
	
	/**
	 * Marshall a TemplateDefault object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A TemplateDefault object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
		
		$expr = $component->getExpression();
		$exprMarshaller = $this->getMarshallerFactory()->createMarshaller($expr);
		$exprElt = $exprMarshaller->marshall($expr);
		
		$element->appendChild($exprElt);
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI templateDefault element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A templateDefault object.
	 * @throws UnmarshallingException If the mandatory attribute 'templateIdentifier' is missing or has an unexpected number of expressions.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($tplIdentifier = static::getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
			
			$expressionElt = self::getFirstChildElement($element);
			
			if ($expressionElt !== false) {
				$exprMarshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
				$expr = $exprMarshaller->unmarshall($expressionElt);
			}
			else {
				$msg = "Element '" . $element->nodeName . "' does not contain its mandatory expression.";
				throw new UnmarshallingException($msg, $element);
			}
			
			$object = new TemplateDefault($tplIdentifier, $expr);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'templateIdentifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'templateDefault';
	}
}