<?php

namespace qtism\data\storage\xml;

use qtism\common\collections\AbstractCollection;
use InvalidArgumentException as InvalidArgumentException;
use \LibXMLError as LibXMLError;

/**
 * A collection that aims at storing LibXMLError objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @see libxml_get_errors
 */
class LibXmlErrorCollection extends AbstractCollection {

	/**
	 * Check if $value is a LibXMLError object.
	 * 
	 * @throws InvalidArgumentException If $value is not a LibXMLError object.
	 */
	protected function checkType($value) {
		if (!$value instanceof LibXMLError) {
			$msg = "LibXmlErrorCollection class only accept LibXMLError objects.";
			throw new InvalidArgumentException($msg);
		}
	}
}