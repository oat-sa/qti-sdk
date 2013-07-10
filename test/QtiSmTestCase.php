<?php
require_once(dirname(__FILE__) . '/../qtism/qtism.php');

use qtism\data\AssessmentTest;
use qtism\data\storage\xml\marshalling\MarshallerFactory;
use \DOMElement;

abstract class QtiSmTestCase extends PHPUnit_Framework_TestCase {
	
	private $marshallerFactory;
	
	public function setUp() {
		$this->marshallerFactory = new MarshallerFactory();
	}
	
	public function getMarshallerFactory() {
		return $this->marshallerFactory;
	}
	
	/**
	 * Returns the canonical path to the samples directory, with the
	 * trailing slash.
	 * 
	 * @return string
	 */
	public static function samplesDir() {
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Create a DOMElement from an XML string.
	 * 
	 * @param unknown_type $xmlString A string containing XML markup
	 * @return DOMElement The according DOMElement;
	 */
	public static function createDOMElement($xmlString) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML($xmlString);
		return $dom->documentElement;
	}
	
	/**
	 * Create a QtiComponent object from an XML String.
	 *
	 * @param string $xmlString An XML String to transform in a QtiComponent object.
	 * @return \qtism\data\QtiComponent
	 */
	public function createComponentFromXml($xmlString) {
		$element = QtiSmTestCase::createDOMElement($xmlString);
		$factory = $this->getMarshallerFactory();
		$marshaller = $factory->createMarshaller($element);
		return $marshaller->unmarshall($element);
	}
}