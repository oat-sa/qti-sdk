<?php
namespace qtismtest;

use qtism\common\utils\Version;
use qtism\data\AssessmentTest;
use qtism\data\storage\xml\marshalling\Qti20MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti21MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti211MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti22MarshallerFactory;
use \DOMElement;
use \DOMDocument;
use \DateTime;
use \DateTimeZone;

abstract class QtiSmTestCase extends \PHPUnit_Framework_TestCase {
	
	public function setUp() {
	    parent::setUp();
	}
	
	public function tearDown() {
	    parent::tearDown();
	}
	
	public function getMarshallerFactory($version = '2.1') {
	    if (Version::compare($version, '2.0.0', '==') === true) {
	        return new Qti20MarshallerFactory();
	    } elseif (Version::compare($version, '2.1.1', '==') === true) {
	        return new Qti211MarshallerFactory();
	    } elseif (Version::compare($version, '2.2.0', '==') === true) {
	        return new Qti22MarshallerFactory();
	    } else {
	        return new Qti21MarshallerFactory();
	    }
	}
	
	/**
	 * Returns the canonical path to the samples directory, with the
	 * trailing slash.
	 * 
	 * @return string
	 */
	public static function samplesDir() {
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Create a directory in OS temp directory with a unique name.
	 * 
	 * @return string The path to the created directory.
	 */
	public static function tempDir() {
	    $tmpFile = tempnam(sys_get_temp_dir(), 'qsm');
	    
	    // Tempnam creates a file with 600 chmod. Remove
	    // it and create a directory.
	    if (file_exists($tmpFile) === true) {
	        unlink($tmpFile);
	    }
	    
	    mkdir($tmpFile);
	    
	    return $tmpFile;
	}
	
	/**
	 * Create a copy of $source to the temp directory. The copied
	 * file will receive a unique file name.
	 * 
	 * @param string $source The source file to be copied.
	 * @return string The path to the copied file.
	 */
	public static function tempCopy($source) {
	    $tmpFile = tempnam(sys_get_temp_dir(), 'qsm');
	    
	    // Same as for QtiSmTestCase::tempDir...
	    if (file_exists($tmpFile) === true) {
	        unlink($tmpFile);
	    }
	    
	    copy($source, $tmpFile);
	    
	    return $tmpFile;
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
	 * Create a DateTime object from a $date with format
	 * Y-m-d H:i:s, and an optional timezone name.
	 * 
	 * @param string $date A date
	 * @param string $tz A timezone name.
	 * @return Date
	 */
	public static function createDate($date, $tz = 'UTC') {
	    return DateTime::createFromFormat('Y-m-d H:i:s', $date, new DateTimeZone($tz));
	}
	
	/**
	 * Create a QtiComponent object from an XML String.
	 *
	 * @param string $xmlString An XML String to transform in a QtiComponent object.
	 * @param string $version A QTI version rule the creation of the component.
	 * @return \qtism\data\QtiComponent
	 */
	public function createComponentFromXml($xmlString, $version = '2.1.0') {
		$element = QtiSmTestCase::createDOMElement($xmlString);
		$factory = $this->getMarshallerFactory($version);
		$marshaller = $factory->createMarshaller($element);
		return $marshaller->unmarshall($element);
	}
}