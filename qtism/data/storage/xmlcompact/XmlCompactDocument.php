<?php

namespace qtism\data\storage\xmlcompact;

use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xmlcompact\marshalling\CompactMarshallerFactory;
use \DOMElement;

class XmlCompactDocument extends XmlDocument {
	
	/**
	 * Override of XmlDocument::createMarshallerFactory in order
	 * to return an appropriate CompactMarshallerFactory.
	 * 
	 * @return CompactMarshallerFactory A CompactMarshallerFactory object.
	 */
	protected function createMarshallerFactory() {
		return new CompactMarshallerFactory();
	}
	
	/**
	 * Override of XmlDocument.
	 * 
	 * Specifices the correct XSD schema locations and main namespace
	 * for the root element of a Compact XML document.
	 */
	public function decorateRootElement(DOMElement $rootElement) {
		$rootElement->setAttribute('xmlns', "http://www.imsglobal.org/xsd/imsqti_v2p1");
		$rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$rootElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', "http://www.taotesting.com/xsd/qticompact_v1p0.xsd");
	}
}