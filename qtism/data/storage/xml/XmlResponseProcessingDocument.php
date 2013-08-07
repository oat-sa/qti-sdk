<?php

namespace qtism\data\storage\xml;

use qtism\data\state\ResponseProcessing;
use \DOMDocument;
use \RuntimeException;

class XmlResponseProcessingDocument extends ResponseProcessing implements IXmlDocument {
	
	/**
	 * The QTI version in use.
	 * 
	 * @var string
	 */
	private $version = '2.1';
	
	/**
	 * The XmlDocument object corresponding to the saved/loaded ResponseProcessing.
	 * 
	 * @var XmlDocument
	 */
	private $xmlDocument = null;
	
	/**
	 * Create a new XmlAssessmentItemDocument object with a default identifier, which
	 * is not time dependent.
	 * 
	 * @param string $version The QTI version to use (default is '2.1').
	 */
	public function __construct($version = '2.1') {
		parent::__construct();
		$this->setXmlDocument(new XmlDocument($version, $this));
	}
	
	public function load($uri, $validate = false) {
		$this->getXmlDocument()->load($uri, $validate);
	}
	
	public function save($uri) {
		$this->getXmlDocument()->getXmlDocument()->save($uri);
	}
	
	public function getUri() {
		return $this->getXmlDocument()->getUri();
	}
	
	public function setVersion($version) {
		$this->getXmlDocument()->setVersion($version);
	}
	
	public function getVersion() {
		return $this->getXmlDocument()->getVersion();
	}
	
	protected function setXmlDocument(XmlDocument $xmlDocument) {
		$this->xmlDocument = $xmlDocument;
	}
	
	public function getXmlDocument() {
		return $this->xmlDocument;
	}
	
	/**
	 * Validate the ResponseProcessing XML document according to the relevant XSD schema.
	 * If $filename is provided, the file pointed by $filename will be used instead
	 * of the default schema.
	 */
	public function schemaValidate($filename = '') {
		if (empty($filename)) {
			// default xsd for AssessmentItem? v2? v2p1?
			$filename = Utils::getSchemaLocation($this->getVersion());
		}
	
		$this->getXmlDocument()->schemaValidate($filename);
	}
}