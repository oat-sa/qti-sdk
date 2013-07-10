<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\NumberPresented;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI numberPresented expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberPresentedMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an NumberPresented object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A NumberPresented object.
	 * @return DOMElement The corresponding numberPresented QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		return $element;
	}
	
	/**
	 * Marshall an numberPresented QTI element in its NumberPresented object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding NumberPresented object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		$object = new NumberPresented();
		$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
		$object->setIncludeCategories($baseComponent->getIncludeCategories());
		$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'numberPresented';
	}
}