<?php

namespace qtism\data\storage\xml;

use qtism\data\Document;

/**
 * The interface an XML Document should expose.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface IXmlDocument extends Document {
	
	/**
	 * Returns the loaded/saved XmlDocument object.
	 * 
	 * @return XmlDocument The loaded/saved XmlDocument object.
	 * 
	 */
	public function getXmlDocument();
	
	/**
	 * Validate the XML document according to the relevant schema.
	 * 
	 * @param string $filename Force the schema to use located at $filename.
	 * @throws XmlStorageException If the validation fails.
	 */
	public function schemaValidate($filename = '');
}