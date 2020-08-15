<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use DOMDocument;
use DOMElement;
use DOMException;
use InvalidArgumentException;
use LibXMLError;
use LogicException;
use qtism\common\utils\Url;
use qtism\data\content\Flow;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponentIterator;
use qtism\data\QtiDocument;
use qtism\data\storage\xml\marshalling\MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti20MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti211MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti21MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti221MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti22MarshallerFactory;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtism\data\storage\xml\Utils as XmlUtils;
use qtism\data\TestPart;
use ReflectionClass;
use RuntimeException;

/**
 * This class represents a QTI-XML Document.
 */
class XmlDocument extends QtiDocument
{
    /**
     * The produced domDocument after a successful call to
     * XmlDocument::load or XmlDocument::save.
     *
     * @var DOMDocument
     */
    private $domDocument = null;

    /**
     * Create a new XmlDocument.
     *
     * If the given QTI $version number is given with no patch version (c.f. Semantic Versioning), 0 will be used as the patch
     * version.
     *
     * @param string $version The version number of the QTI specfication to use in order to load or save an AssessmentTest.
     * @param QtiComponent $documentComponent (optional) A QtiComponent object to be bound to the QTI XML document to save.
     * @throws InvalidArgumentException If $version is not a known QTI version.
     */
    public function __construct($version = '2.1', QtiComponent $documentComponent = null)
    {
        parent::__construct($version, $documentComponent);
    }

    /**
     * Set the DOMDocument object in use.
     *
     * @param DOMDocument $domDocument A DOMDocument object.
     */
    protected function setDomDocument(DOMDocument $domDocument)
    {
        $this->domDocument = $domDocument;
    }

    /**
     * Get the DOMDocument object in use.
     *
     * @return DOMDocument
     */
    public function getDomDocument()
    {
        return $this->domDocument;
    }

    /**
     * Load a QTI-XML assessment file. The file will be loaded and represented in
     * an AssessmentTest object.
     *
     * @param string $uri The Uniform Resource Identifier that identifies/locate the file.
     * @param boolean $validate Whether or not the file must be validated unsing XML Schema? Default is false.
     * @throws XmlStorageException If an error occurs while loading the QTI-XML file.
     */
    public function load($uri, $validate = false)
    {
        $this->loadImplementation($uri, $validate, false);

        // We now are sure that the URI is valid.
        $this->setUrl($uri);
    }

    /**
     * Load QTI-XML from string.
     *
     * @param string $string The QTI-XML string.
     * @param boolean $validate XML Schema validation? Default is false.
     * @throws XmlStorageException If an error occurs while parsing $string.
     */
    public function loadFromString($string, $validate = false)
    {
        $this->loadImplementation($string, $validate, true);
    }

    /**
     * Implementation of load.
     *
     * @param mixed $data
     * @param boolean $validate
     * @param boolean $fromString
     * @throws XmlStorageException
     */
    protected function loadImplementation($data, $validate = false, $fromString = false)
    {
        try {
            $this->setDomDocument(new DOMDocument('1.0', 'UTF-8'));
            $this->getDomDocument()->preserveWhiteSpace = true;

            // Disable xml warnings and errors and fetch error information as needed.
            $oldErrorConfig = libxml_use_internal_errors(true);

            // Determine which way to load (from string or from file).
            $loadMethod = ($fromString === true) ? 'loadXML' : 'load';

            $doc = $this->getDomDocument();

            if (call_user_func_array([$doc, $loadMethod], [$data, LIBXML_COMPACT | LIBXML_NONET | LIBXML_XINCLUDE | LIBXML_PARSEHUGE])) {
                // Infer the QTI version.
                if (($version = XmlUtils::inferVersion($this->getDomDocument())) !== false) {
                    $this->setVersion($version);
                } else {
                    $msg = "Cannot infer QTI version. Is it well formed?";
                    throw new XmlStorageException($msg);
                }

                if ($validate === true) {
                    $this->schemaValidate();
                }

                try {
                    // Get the root element and unmarshall.
                    $element = $this->getDomDocument()->documentElement;
                    $factory = $this->createMarshallerFactory();
                    $marshaller = $factory->createMarshaller($element);
                    $this->setDocumentComponent($marshaller->unmarshall($element));
                } catch (UnmarshallingException $e) {
                    $line = $e->getDOMElement()->getLineNo();
                    $msg = "An error occured while processing QTI-XML at line ${line}.";
                    throw new XmlStorageException($msg, $e);
                } catch (RuntimeException $e) {
                    $msg = "Unmarshallable element '" . $element->localName . "' in QTI-XML.";
                    throw new XmlStorageException($msg, $e);
                }
            } else {
                $libXmlErrors = libxml_get_errors();
                $formattedErrors = self::formatLibXmlErrors($libXmlErrors);

                libxml_clear_errors();
                libxml_use_internal_errors($oldErrorConfig);

                $msg = "An internal error occured while parsing QTI-XML:\n${formattedErrors}";
                throw new XmlStorageException($msg, null, new LibXmlErrorCollection($libXmlErrors));
            }
        } catch (DOMException $e) {
            $line = $e->getLine();
            $msg = "An error occured while parsing QTI-XML at line ${line}.";
            throw new XmlStorageException($msg, $e);
        }
    }

    /**
     * This method can be overriden by subclasses in order to alter a last
     * time the data model prior to be saved.
     *
     * @param QtiComponent $documentComponent The root component of the model that will be saved.
     * @param string $uri The URI where the saved file is supposed to be stored.
     * @throws XmlStorageException If something wrong occurs.
     */
    protected function beforeSave(QtiComponent $documentComponent, $uri)
    {
        return;
    }

    /**
     * Save the Assessment Document at the location described by $uri. Please be carefull
     * to provide an AssessmentTest object to save before calling this method.
     *
     * In case of a Filesystem object being injected prior to the call, data will be stored on through
     * this Filesystem implementation. Otherwise, it will be stored on the local filesystem.
     *
     * @param string $uri The URI describing the location to save the QTI-XML representation of the Assessment Test.
     * @param boolean $formatOutput Whether the XML content of the file must be formatted (new lines, indentation) or not.
     * @throws XmlStorageException If an error occurs while transforming the AssessmentTest object to its QTI-XML representation.
     */
    public function save($uri, $formatOutput = true)
    {
        $this->saveImplementation($uri, $formatOutput);
    }

    /**
     * Save the Assessment Document as an XML string.
     *
     * @param boolean $formatOutput Whether the XML content of the file must be formatted (new lines, indentation) or not.
     * @return string The XML string.
     * @throws XmlStorageException If an error occurs while transforming the AssessmentTest object to its QTI-XML representation.
     */
    public function saveToString($formatOutput = true)
    {
        return $this->saveImplementation('', $formatOutput);
    }

    /**
     * Implementation of save.
     *
     * @param string $uri
     * @param boolean $formatOutput
     * @return string
     * @throws XmlStorageException
     */
    protected function saveImplementation($uri = '', $formatOutput = true)
    {
        $assessmentTest = $this->getDocumentComponent();

        if (!empty($assessmentTest)) {
            $this->setDomDocument(new DOMDocument('1.0', 'UTF-8'));

            if ($formatOutput == true) {
                $this->getDomDocument()->formatOutput = true;
            }

            try {
                // If overriden, beforeSave may alter a last time
                // the documentComponent prior serialization.
                // Note: in use only when saving to a file.
                if (empty($uri) === false) {
                    $this->beforeSave($this->getDocumentComponent(), $uri);
                }

                $factory = $this->createMarshallerFactory();
                $marshaller = $factory->createMarshaller($this->getDocumentComponent());
                $element = $marshaller->marshall($this->getDocumentComponent());

                $rootElement = $this->getDomDocument()->importNode($element, true);
                $this->getDomDocument()->appendChild($rootElement);
                $this->decorateRootElement($rootElement);

                if (empty($uri) === false) {
                    if ($this->getDomDocument()->save($uri) === false) {
                        // An error occured while saving.
                        $msg = "An internal error occured while saving QTI-XML file at '${uri}'.";
                        throw new XmlStorageException($msg);

                        $this->setUrl($uri);
                    }
                } else {
                    if (($strXml = $this->getDomDocument()->saveXML()) !== false) {
                        return $strXml;
                    } else {
                        // An error occured while saving.
                        $msg = "An internal error occured while exporting QTI-XML as string.";
                        throw new XmlStorageException($msg);
                    }
                }
            } catch (DOMException $e) {
                $msg = "An internal error occured while saving QTI-XML data.";
                throw new XmlStorageException($msg, $e);
            } catch (XmlStorageException $e) {
                $msg = "An error occured before saving QTI-XML data. Make sure the implementation of XmlDocument::beforeSave() is correct.";
                throw new XmlStorageException($msg, $e);
            }
        } else {
            $msg = "The Assessment Document cannot be saved. No AssessmentTest object provided.";
            throw new XmlStorageException($msg);
        }
    }

    /**
     * Validate the document against a schema.
     *
     * @param string $filename An optional filename of a given schema the document should validate against.
     * @throws XmlStorageException
     * @throws InvalidArgumentException
     */
    public function schemaValidate($filename = '')
    {
        if (empty($filename)) {
            $filename = XmlUtils::getSchemaLocation($this->getVersion());
        }

        if (is_readable($filename)) {
            $oldErrorConfig = libxml_use_internal_errors(true);

            $doc = $this->getDomDocument();
            if (@$doc->schemaValidate($filename) === false) {
                $libXmlErrors = libxml_get_errors();
                $formattedErrors = self::formatLibXmlErrors($libXmlErrors);

                libxml_clear_errors();
                libxml_use_internal_errors($oldErrorConfig);

                $msg = "The document could not be validated with XML Schema '${filename}':\n${formattedErrors}";
                throw new XmlStorageException($msg, null, new LibXmlErrorCollection($libXmlErrors));
            }
        } else {
            $msg = "Schema '${filename}' cannot be read. Does this file exist? Is it readable?";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Resolve include components.
     *
     * After the item has been loaded using the load or loadFromString method,
     * the include components can be resolved by calling this method. Files will
     * be included following the rules described by the XInclude specification.
     *
     * @param boolean $validate Whether or not validate files being included. Default is false.
     * @throws LogicException If the method is called prior the load or loadFromString method was called.
     * @throws XmlStorageException If an error occured while parsing or validating files to be included.
     */
    public function xInclude($validate = false)
    {
        if (($root = $this->getDocumentComponent()) !== false) {
            $baseUri = str_replace('\\', '/', $this->getDomDocument()->documentElement->baseURI);
            $pathinfo = pathinfo($baseUri);
            $basePath = $pathinfo['dirname'];

            $iterator = new QtiComponentIterator($root, ['include']);
            foreach ($iterator as $include) {
                $parent = $iterator->parent();

                // Is the parent something we can deal with for replacement?
                $reflection = new ReflectionClass($parent);

                if ($reflection->hasMethod('getContent') === true && $parent->getContent() instanceof QtiComponentCollection) {
                    $href = $include->getHref();

                    if (Url::isRelative($href) === true) {
                        $href = Url::rtrim($basePath) . '/' . Url::ltrim($href);

                        $doc = new XmlDocument();
                        $doc->load($href, $validate);
                        $includeRoot = $doc->getDocumentComponent();

                        if ($includeRoot instanceof Flow) {
                            // Derive xml:base...
                            $xmlBase = Url::ltrim(str_replace($basePath, '', $href));
                            $xmlBasePathInfo = pathinfo($xmlBase);

                            if ($xmlBasePathInfo['dirname'] !== '.') {
                                $includeRoot->setXmlBase($xmlBasePathInfo['dirname'] . '/');
                            }
                        }

                        $parent->getContent()->replace($include, $includeRoot);
                    }
                }
            }
        } else {
            $msg = "Cannot include fragments via XInclude before loading any file.";
            throw new LogicException($msg);
        }
    }

    /**
     * Include assessmentSectionRefs component in the current document.
     *
     * This method includes the assessmentSectionRefs components in the current document. The references
     * to assessmentSections are resolved. assessmentSectionRefs will be replaced with their assessmentSection
     * content.
     *
     * @param boolean $validate (optional) Whether or not validate the content of included assessmentSectionRefs.
     * @throws LogicException If the method is called prior the load or loadFromString method was called.
     * @throws XmlStorageException If an error occured while parsing or validating files to be included.
     */
    public function includeAssessmentSectionRefs($validate = false)
    {
        if (($root = $this->getDocumentComponent()) !== null) {
            $baseUri = str_replace('\\', '/', $this->getDomDocument()->documentElement->baseURI);
            $pathinfo = pathinfo($baseUri);
            $basePath = $pathinfo['dirname'];

            $count = count($root->getComponentsByClassName('assessmentSectionRef'));
            while ($count > 0) {
                $iterator = new QtiComponentIterator($root, ['assessmentSectionRef']);
                foreach ($iterator as $assessmentSectionRef) {
                    $parent = $iterator->parent();
                    $href = $assessmentSectionRef->getHref();

                    if (Url::isRelative($href) === true) {
                        $href = Url::rtrim($basePath) . '/' . Url::ltrim($href);

                        $doc = new XmlDocument();
                        $doc->load($href, $validate);

                        $sectionRoot = $doc->getDocumentComponent();

                        foreach ($sectionRoot->getComponentsByClassName(['assessmentSectionRef', 'assessmentItemRef']) as $sectionPart) {
                            $newBasePath = Url::ltrim(str_replace($basePath, '', $href));
                            $pathinfo = pathinfo($newBasePath);
                            $newHref = $pathinfo['dirname'] . '/' . Url::ltrim($sectionPart->getHref());
                            $sectionPart->setHref($newHref);
                        }

                        if ($parent instanceof TestPart) {
                            $collection = $parent->getAssessmentSections();
                        } else {
                            $collection = $parent->getSectionParts();
                        }

                        $collection->replace($assessmentSectionRef, $sectionRoot);
                    }
                }

                $count = count($root->getComponentsByClassName('assessmentSectionRef'));
            }
        } else {
            $msg = "Cannot resolve assessmentSectionRefs before loading any file.";
            throw new LogicException($msg);
        }
    }

    /**
     * Decorate the root element of the XmlAssessmentDocument with the appropriate
     * namespaces and schema definition.
     *
     * @param DOMElement $rootElement The root DOMElement object of the document to decorate.
     */
    protected function decorateRootElement(DOMElement $rootElement)
    {
        $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd';
        $xmlns = "http://www.imsglobal.org/xsd/imsqti_v2p1";

        switch (trim($this->getVersion())) {
            case '2.0.0':
                $xsdLocation = 'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd';
                $xmlns = "http://www.imsglobal.org/xsd/imsqti_v2p0";
                break;

            case '2.1.0':
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd';
                $xmlns = "http://www.imsglobal.org/xsd/imsqti_v2p1";
                break;

            case '2.1.1':
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1p1.xsd';
                $xmlns = "http://www.imsglobal.org/xsd/imsqti_v2p1";
                break;

            case '2.2.0':
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2.xsd';
                $xmlns = "http://www.imsglobal.org/xsd/imsqti_v2p2";
                break;

            case '2.2.1':
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2p1.xsd';
                $xmlns = "http://www.imsglobal.org/xsd/imsqti_v2p2";
                break;
        }

        $rootElement->setAttribute('xmlns', $xmlns);
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $rootElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', "${xmlns} ${xsdLocation}");
    }

    /**
     * Format some $libXmlErrors into an array of strings instead of an array of arrays.
     *
     * @param LibXMLError[] $libXmlErrors
     * @return string
     */
    protected static function formatLibXmlErrors(array $libXmlErrors)
    {
        $formattedErrors = [];

        foreach ($libXmlErrors as $error) {
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    // Since QTI 2.2, some schemas are imported multiple times.
                    // Xerces does not produce errors, but libxml does...
                    if (preg_match('/Skipping import of schema located/ui', $error->message) === 0) {
                        $formattedErrors[] = "Warning: " . trim($error->message) . " at " . $error->line . ":" . $error->column . ".";
                    }

                    break;

                case LIBXML_ERR_ERROR:
                    $formattedErrors[] = "Error: " . trim($error->message) . " at " . $error->line . ":" . $error->column . ".";
                    break;

                case LIBXML_ERR_FATAL:
                    $formattedErrors[] = "Fatal Error: " . trim($error->message) . " at " . $error->line . ":" . $error->column . ".";
                    break;
            }
        }

        $formattedErrors = implode("\n", $formattedErrors);

        return $formattedErrors;
    }

    /**
     * MarshallerFactory factory method (see gang of four).
     *
     * @return MarshallerFactory An appropriate MarshallerFactory object.
     */
    protected function createMarshallerFactory()
    {
        $version = $this->getVersion();
        if ($version === '2.0.0') {
            return new Qti20MarshallerFactory();
        } elseif ($version === '2.1.0') {
            return new Qti21MarshallerFactory();
        } elseif ($version === '2.1.1') {
            return new Qti211MarshallerFactory();
        } elseif ($version === '2.2.0') {
            return new Qti22MarshallerFactory();
        } elseif ($version === '2.2.1') {
            return new Qti221MarshallerFactory();
        } else {
            $msg = "No MarshallerFactory implementation found for QTI version '${version}'.";
            throw new RuntimeException($msg);
        }
    }

    /**
     * Infer the QTI version of the document from its XML definition.
     *
     * @return boolean|string false if cannot be inferred otherwise a semantic version of the QTI version with major, minor and patch versions e.g. '2.1.0'.
     */
    protected function inferVersion()
    {
        return XmlUtils::inferVersion($this->getDomDocument());
    }
}
