<?php

namespace qtism\data\storage\xml;

use qtism\data\AssessmentSection;
use \DOMDocument;

class XmlAssessmentSectionDocument extends AssessmentSection implements IXmlDocument {
	
	/**
	 * The XmlDocument object corresponding to the saved/loaded AssessmentTest.
	 * 
	 * @var DOMDocument
	 */
	private $xmlDocument;
	
	/**
	 * Create a new XmlAssessmentSectionDocument object with a default identifier, title with
	 * visibility set to (boolean) true.
	 * 
	 * @param string $version The QTI version to use (default is '2.1').
	 */
	public function __construct($version = '2.1') {
		parent::__construct('assessmentSection', 'A QTI Assessment Section', true);
		$this->setXmlDocument(new XmlDocument($version, $this));
	}
	
	public function load($uri, $validate = false) {
		$this->getXmlDocument()->load($uri, $validate);
	}
	
	public function save($uri) {
		$this->getXmlDocument()->save($uri);
	}
	
	public function setVersion($version) {
		$this->getXmlDocument()->setVersion($version);
	}
	
	public function getVersion() {
		return $this->getXmlDocument()->getVersion();
	}
	
	public function getUri() {
		return $this->getXmlDocument()->getUri();
	}
	
	protected function setXmlDocument(XmlDocument $xmlDocument) {
		$this->xmlDocument = $xmlDocument;
	}
	
	public function getXmlDocument() {
		return $this->xmlDocument;
	}
	
	/**
	 * Validate the AssessmentSection XML document according to the relevant XSD schema.
	 * If $filename is provided, the file pointed by $filename will be used instead
	 * of the default schema.
	 */
	public function schemaValidate($filename = '') {
		if (empty($filename)) {
			$filename = Utils::getSchemaLocation('2.1');
		}
	
		$this->getXmlDocument()->schemaValidate($filename);
	}
}