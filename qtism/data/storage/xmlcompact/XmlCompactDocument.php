<?php

namespace qtism\data\storage\xmlcompact;

use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xmlcompact\marshalling\CompactMarshallerFactory;

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
}