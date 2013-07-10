<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\NumberCorrect;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI numberCorrect expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberCorrectMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an NumberCorrect object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A NumberCorrect object.
	 * @return DOMElement The corresponding numberCorrect QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		return $element;
	}
	
	/**
	 * Marshall an numberCorrect QTI element in its NumberCorrect object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding NumberCorrect object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		
		// Please PHP core development team, give us real method overloading !!! :'(
		$object = new NumberCorrect();
		$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
		$object->setIncludeCategories($baseComponent->getIncludeCategories());
		$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'numberCorrect';
	}
}