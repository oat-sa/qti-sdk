<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\rules\ExitTest;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for exitTest.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitTestMarshaller extends Marshaller {
	
	/**
	 * Marshall an ExitTest object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An ExitTest object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI exitTest element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent An ExitTest object.
	 */
	protected function unmarshall(DOMElement $element) {
		$object = new ExitTest();
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'exitTest';
	}
}