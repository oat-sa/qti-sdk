<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\rules\ExitResponse;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for exitResponse.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitResponseMarshaller extends Marshaller {
	
	/**
	 * Marshall an ExitResponse object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An ExitResponse object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI exitResponse element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent An ExitResponse object.
	 */
	protected function unmarshall(DOMElement $element) {
		$object = new ExitResponse();
		return $object;
	}
	
	public function getExpectedQtiClassName() {
		return 'exitResponse';
	}
}