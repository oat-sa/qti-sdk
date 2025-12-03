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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2013-2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use DOMDocument;
use DOMElement;
use DOMException;
use Exception;
use InvalidArgumentException;
use League\Flysystem\Filesystem;
use LogicException;
use qtism\common\dom\SerializableDomDocument;
use qtism\common\utils\Url;
use qtism\data\AssessmentItem;
use qtism\data\BranchRuleTargetException;
use qtism\data\content\Flow;
use qtism\data\expressions\Variable;
use qtism\data\processing\ResponseProcessing;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponentIterator;
use qtism\data\QtiDocument;
use qtism\data\rules\BranchRule;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\storage\xml\filesystem\FilesystemFactory;
use qtism\data\storage\xml\filesystem\FilesystemInterface;
use qtism\data\storage\xml\filesystem\FilesystemException;
use qtism\data\storage\xml\filesystem\FlysystemV1Filesystem;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtism\data\storage\xml\versions\QtiVersion;
use qtism\data\TestPart;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * This class represents a QTI-XML Document.
 */
class XmlDocument extends QtiDocument
{
    /**
     * Lib Xml configuration flags for Xml loading.
     */
    public const LIB_XML_FLAGS = LIBXML_COMPACT | LIBXML_NONET | LIBXML_XINCLUDE | LIBXML_BIGLINES | LIBXML_PARSEHUGE;

    private const REF_ELEMENTS = ['assessmentSectionRef', 'assessmentItemRef'];

    /**
     * The produced domDocument after a successful call to
     * XmlDocument::load or XmlDocument::save.
     *
     * @var SerializableDomDocument
     */
    private $domDocument;

    /**
     * The Filesystem implementation in use. Contains null
     * in case of old fashioned local file system implementation.
     *
     * @var null|FilesystemInterface
     */
    private $filesystem;

    public function __toString(): string
    {
        return $this->saveToString(false);
    }

    /**
     * Set the SerializableDomDocument object in use.
     *
     * @param SerializableDomDocument $domDocument A SerializableDomDocument object.
     */
    protected function setDomDocument(SerializableDomDocument $domDocument): void
    {
        $this->domDocument = $domDocument;
    }

    /**
     * Get the DOMDocument object in use.
     *
     * @return DOMDocument
     */
    public function getDomDocument(): DOMDocument
    {
        return $this->domDocument->getDom();
    }

    /**
     * Set the Filesystem implementation.
     *
     * Set the Filesystem implementation to be used. As soon as an implementation is set,
     * all subsequent creations of related XmlDocument objects will use this Filesystem
     * implementation.
     *
     * @param Filesystem|FilesystemInterface|null $filesystem
     */
    public function setFilesystem(?FilesystemInterface $filesystem = null): void
    {
        if (!$filesystem) {
            $filesystem = FilesystemFactory::local();
        }

        // Support backwards compatibility of old Flysystem v1 Filesystems being passed into this class
        if ($filesystem instanceof Filesystem && FilesystemFactory::isFlysystemV1Installed()) {
            $filesystem = new FlysystemV1Filesystem($filesystem);
        } elseif (!$filesystem instanceof FilesystemInterface) {
            throw new RuntimeException(
                sprintf(
                    'Invalid filesystem provided.  Instance of %s required',
                    FilesystemInterface::class,
                )
            );
        }

        $this->filesystem = $filesystem;
    }

    /**
     * Get the Filesystem implementation.
     *
     * Get the Filesystem implementation currently in use. Returns null
     * in case of the local file system is in use.
     *
     * @return FilesystemInterface|null
     */
    protected function getFilesystem(): ?FilesystemInterface
    {
        if ($this->filesystem === null) {
            $this->setFilesystem();
        }
        return $this->filesystem;
    }

    /**
     * Load a QTI-XML assessment file. The file will be loaded and represented in
     * an AssessmentTest object.
     *
     * If the XmlDocument object was previously with a QTI version which does not
     * correspond to version in use in the loaded file, the version found into
     * the file will supersede the version specified at instantiation time.
     *
     * In case of a Filesystem object being injected prior to the call via the XmlDocument::setFilesystem()
     * method, the data will be loaded through this implementation. Otherwise, it will be loaded from the
     * local filesystem.
     *
     * @param string $url The Uniform Resource Identifier that identifies/locate the file.
     * @param bool $validate Whether the file must be validated unsing XML Schema? Default is false.
     * @throws XmlStorageException If an error occurs while loading the QTI-XML file.
     */
    public function load(string $url, bool $validate = false): void
    {
        $this->loadImplementation($this->loadFromFile($url), $validate);
        $this->setUrl($url);
    }

    /**
     * Load QTI-XML from string.
     *
     * @param string $string The QTI-XML string.
     * @param bool $validate XML Schema validation? Default is false.
     * @throws XmlStorageException If an error occurs while parsing $string.
     */
    public function loadFromString(string $string, $validate = false): void
    {
        $this->loadImplementation($string, $validate);
        $this->setUrl('');
    }

    /**
     * Implementation of load.
     *
     * @param mixed $data
     * @param bool $validate
     * @throws XmlStorageException
     * @throws BranchRuleTargetException
     */
    protected function loadImplementation($data, bool $validate): void
    {
        // Pre-check to throw an appropriate exception when load from an empty string.
        if ($data === '') {
            $msg = 'Cannot load QTI from an empty string.';
            throw new XmlStorageException($msg, XmlStorageException::READ);
        }

        $doc = new SerializableDomDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = true;
        $this->setDomDocument($doc);

        $this->loadXml($data);

        // Infer the QTI version.
        try {
            // Prefers the version contained in the XML payload if valid.
            $this->setVersion($this->inferVersion());
        } catch (XmlStorageException $exception) {
            // If not valid, keeps the version set on object creation.
        }

        if ($validate) {
            $this->schemaValidate();
        }

        // Unmarshalls the root element.
        $component = $this->unmarshallElement($this->domDocument->documentElement);

        if ($validate) {
            $this->validateDocComponent($component);
        }

        $this->setDocumentComponent($component);
    }

    /**
     * @param DOMElement $element
     * @return QtiComponent
     * @throws XmlStorageException
     */
    protected function unmarshallElement(DomElement $element): QtiComponent
    {
        $factory = $this->version->getMarshallerFactory();

        // MarshallerNotFoundException can happen when creating the marshaller
        // for the main element, but also when creating a marshaller for an
        // element embedded in the main element.
        try {
            return $factory->createMarshaller($element)->unmarshall($element);
        } catch (UnmarshallingException $e) {
            $line = $e->getDOMElement()->getLineNo();
            $msg = "An error occurred while processing QTI-XML at line {$line}.";
            throw new XmlStorageException($msg, XmlStorageException::READ, $e);
        } catch (MarshallerNotFoundException $e) {
            throw XmlStorageException::unsupportedComponentInVersion($e, $this->getVersion());
        }
    }

    /**
     * Loads xml payload in DomDocument, formats errors in any.
     *
     * @param $data
     * @throws XmlStorageException
     */
    protected function loadXml($data): void
    {
        Utils::executeSafeXmlCommand(
            function () use ($data): void {
                $this->domDocument->loadXML($data, self::LIB_XML_FLAGS);
            },
            'An internal error occurred while parsing QTI-XML',
            XmlStorageException::READ
        );
    }

    /**
     * Validate the document against a schema.
     *
     * @throws XmlStorageException
     * @throws InvalidArgumentException
     */
    public function schemaValidate(): void
    {
        Utils::executeSafeXmlCommand(
            function (): void {
                $schema = realpath($this->version->getLocalXsd());
                $this->domDocument->schemaValidate($schema);
            },
            "The document could not be validated with XML Schema '" . realpath($this->version->getLocalXsd()) . "'",
            XmlStorageException::XSD_VALIDATION
        );
    }

    /**
     * Save the Assessment Document at the location described by $uri. Please be carefull
     * to provide an AssessmentTest object to save before calling this method.
     *
     * In case of a Filesystem object being injected prior to the call, data will be stored on through
     * this Filesystem implementation. Otherwise, it will be stored on the local filesystem.
     *
     * @param string $url The URL describing the location to save the QTI-XML representation of the Assessment Test.
     * @param bool $formatOutput Whether the XML content of the file must be formatted (new lines, indentation) or not.
     * @throws XmlStorageException If an error occurs while transforming the AssessmentTest object
     *                             into its QTI-XML representation.
     */
    public function save(string $url, bool $formatOutput = true): void
    {
        $this->assertComponentNotNull();

        // If overridden, beforeSave may alter a last time the documentComponent before serialization.
        if ($url !== '') {
            $this->beforeSave($this->getDocumentComponent(), $url);
        }

        $strXml = $this->saveImplementation($formatOutput);

        $this->saveToFile($url, $strXml);
    }

    /**
     * Save the Assessment Document as an XML string.
     *
     * @param bool $formatOutput Whether the XML content of the file must be formatted (new lines, indentation) or not.
     * @return string The XML string.
     * @throws XmlStorageException If an error occurs while transforming the AssessmentTest object
     *                             into its QTI-XML representation.
     */
    public function saveToString(bool $formatOutput = true): string
    {
        $this->assertComponentNotNull();

        return $this->saveImplementation($formatOutput);
    }

    /**
     * @throws XmlStorageException when the document component is null.
     */
    protected function assertComponentNotNull(): void
    {
        if ($this->getDocumentComponent() === null) {
            $msg = 'The document cannot be saved. No document component object to be saved.';
            throw new XmlStorageException($msg, XmlStorageException::WRITE);
        }
    }

    /**
     * This method can be overriden by subclasses in order to alter a last
     * time the data model prior to be saved.
     *
     * @param QtiComponent $documentComponent The root component of the model that will be saved.
     * @param string $uri The URI where the saved file is supposed to be stored.
     */
    protected function beforeSave(QtiComponent $documentComponent, $uri): void
    {
    }

    /**
     * Implementation of save.
     *
     * @param bool $formatOutput
     * @return string
     * @throws XmlStorageException
     */
    protected function saveImplementation($formatOutput = true): string
    {
        $element = $this->marshallElement($this->getDocumentComponent());

        $dom = new SerializableDomDocument('1.0', 'UTF-8');
        if ($formatOutput === true) {
            $dom->formatOutput = true;
        }

        try {
            $rootElement = $dom->importNode($element, true);
            $dom->appendChild($rootElement);
        } catch (DOMException $e) {
            $msg = 'An internal error occurred while saving QTI-XML data.';
            throw new XmlStorageException($msg, XmlStorageException::UNKNOWN, $e);
        }

        $externalNamespaces = $this->setNamespaces($dom);

        $strXml = $dom->saveXML();
        if ($strXml === false) {
            // An error occurred while saving.
            $msg = 'An internal error occurred while exporting QTI-XML as string.';
            throw new XmlStorageException($msg, XmlStorageException::WRITE);
        }

        // Since saveXML doesn't support the LIBXML_NSCLEAN option flag yet,
        // we need to clean redundant namespaces manually.
        $strXml = Utils::cleanRedundantNamespaces($strXml, $externalNamespaces);

        return $strXml;
    }

    /**
     * @param QtiComponent $component
     * @return DOMElement
     * @throws XmlStorageException
     */
    protected function marshallElement(QtiComponent $component): DOMElement
    {
        $factory = $this->version->getMarshallerFactory();

        // MarshallerNotFoundException can happen when creating the marshaller
        // for the main element, but also when creating a marshaller for an
        // element embedded in the main element.
        try {
            return $factory->createMarshaller($component)->marshall($component);
        } catch (MarshallingException $e) {
            $msg = "An error occurred while processing QTI component '{$component->getQtiClassName()}'.";
            throw new XmlStorageException($msg, XmlStorageException::READ, $e);
        } catch (MarshallerNotFoundException $e) {
            throw XmlStorageException::unsupportedComponentInVersion($e, $this->getVersion());
        }
    }

    /**
     * @param string $url
     * @return false|mixed|string
     * @throws XmlStorageException
     */
    protected function loadFromFile(string $url)
    {
        try {
            return $this->getFilesystem()->read($url);
        } catch (FilesystemException $e) {
            throw new XmlStorageException(
                "Cannot load QTI file at path '{$url}'. It does not exist or is not readable.",
                XmlStorageException::RESOLUTION,
                $e
            );
        }
    }

    /**
     * @param string $url
     * @param string $content
     * @return bool
     * @throws XmlStorageException
     */
    protected function saveToFile(string $url, string $content): bool
    {
        try {
            return $this->getFilesystem()->write($url, $content);
        } catch (Exception $e) {
            throw new XmlStorageException(
                "An error occurred while saving QTI-XML file at '{$url}'. Maybe the save location is not reachable?",
                XmlStorageException::WRITE
            );
        }
    }

    /**
     * Resolve include components.
     *
     * After the item has been loaded using the load or loadFromString method,
     * the include components can be resolved by calling this method. Files will
     * be included following the rules described by the XInclude specification.
     *
     * @param bool $validate Whether validate files being included. Default is false.
     * @throws XmlStorageException If an error occurred while parsing or validating files to be included.
     * @throws ReflectionException
     */
    public function xInclude($validate = false): void
    {
        if (($root = $this->getDocumentComponent()) !== null) {
            $baseUri = str_replace('\\', '/', $this->getUrl());
            $pathinfo = pathinfo($baseUri);
            $basePath = $pathinfo['dirname'];

            $iterator = new QtiComponentIterator($root, ['include']);
            foreach ($iterator as $include) {
                $parent = $iterator->parent();

                // Is the parent something we can deal with for replacement?
                $reflection = new ReflectionClass($parent);

                if (
                    $reflection->hasMethod('getContent') === true
                    && $parent->getContent() instanceof QtiComponentCollection
                ) {
                    $href = $include->getHref();

                    if (Url::isRelative($href) === true) {
                        $href = Url::rtrim($basePath) . '/' . Url::ltrim($href);

                        $doc = new self();

                        $doc->setFilesystem($this->getFilesystem());

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
            $msg = 'Cannot include fragments via XInclude before loading any file.';
            throw new LogicException($msg);
        }
    }

    /**
     * Resolve responseProcessing elements with template location.
     *
     * If the root element of the currently loaded QTI file is an assessmentItem element,
     * this method will try to resolve responseProcessing fragments referenced by responseProcessing
     * elements having a templateLocation attribute.
     *
     * @param bool $validate Whether validate files being included. Default is false.
     * @throws LogicException If the method is called prior the load or loadFromString method was called.
     * @throws XmlStorageException If an error occurred while parsing or validating files to be included.
     */
    public function resolveTemplateLocation($validate = false): void
    {
        if (($root = $this->getDocumentComponent()) !== null) {
            if (
                $root instanceof AssessmentItem
                && ($responseProcessing = $root->getResponseProcessing()) !== null
                && ($templateLocation = $responseProcessing->getTemplateLocation()) !== ''
                && Url::isRelative($templateLocation) === true
            ) {
                $baseUri = $this->getUrl();
                $pathinfo = pathinfo($baseUri);
                $basePath = $pathinfo['dirname'];
                $templateLocation = Url::rtrim($basePath) . '/' . Url::ltrim($templateLocation);

                $doc = new self();
                $doc->setFilesystem($this->getFilesystem());
                $doc->load($templateLocation, $validate);

                $newResponseProcessing = $doc->getDocumentComponent();

                if ($newResponseProcessing instanceof ResponseProcessing) {
                    $root->setResponseProcessing($newResponseProcessing);
                } else {
                    $msg = sprintf(
                        "The template at location '%s' is not a document containing a QTI responseProcessing element.",
                        $templateLocation
                    );
                    throw new XmlStorageException($msg, XmlStorageException::RESOLUTION);
                }
            }
        } else {
            $msg = 'Cannot resolve template location before loading any file.';
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
     * @param bool $validate (optional) Whether validate the content of included assessmentSectionRefs.
     * @throws LogicException If the method is called prior the load or loadFromString method was called.
     * @throws XmlStorageException If an error occurred while parsing or validating files to be included.
     */
    public function includeAssessmentSectionRefs($validate = false): void
    {
        if (($root = $this->getDocumentComponent()) !== null) {
            $baseUri = str_replace('\\', '/', $this->getUrl());
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

                        $doc = new self();
                        $doc->setFilesystem($this->getFilesystem());
                        $doc->load($href, $validate);

                        $sectionRoot = $doc->getDocumentComponent();

                        foreach ($sectionRoot->getComponentsByClassName(self::REF_ELEMENTS) as $sectionPart) {
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
            $msg = 'Cannot resolve assessmentSectionRefs before loading any file.';
            throw new LogicException($msg);
        }
    }

    /**
     * Changes to Qti version of the root element.
     *
     * @param string $toVersionNumber
     */
    public function changeVersion(string $toVersionNumber): void
    {
        $this->setVersion($toVersionNumber);
        $this->setNamespaces($this->domDocument);
    }

    /**
     * Decorate the root element of the XmlAssessmentDocument with the appropriate
     * namespaces and schema definition.
     *
     * @param SerializableDomDocument $dom The document to decorate.
     * @return array
     */
    protected function setNamespaces(SerializableDomDocument $dom): array
    {
        $namespace = $this->version->getNamespace();
        $xsdLocation = $this->version->getXsdLocation();
        $schemaLocation = $namespace . ' ' . $xsdLocation;

        $rootElement = $dom->documentElement;
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $namespace);
        $rootElement->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );

        $externalNamespaces = Utils::findExternalNamespaces($dom->saveXml());
        foreach ($externalNamespaces as $prefix => $externalNamespace) {
            $rootElement->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:' . $prefix,
                $externalNamespace
            );

            $externalSchemaLocation = $this->version->getExternalSchemaLocation($prefix);

            if ($externalSchemaLocation !== '') {
                $schemaLocation .= ' ' . $externalNamespace . ' ' . $externalSchemaLocation;
            }
        }
        $rootElement->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            $schemaLocation
        );

        return $externalNamespaces;
    }

    /**
     * Infer the QTI version of the document from its XML definition.
     *
     * @return string a semantic version inferred from the document.
     * @throws XmlStorageException when the version can not be inferred.
     */
    protected function inferVersion(): string
    {
        return QtiVersion::infer($this->getDomDocument());
    }

    /**
     * Validate the document component after unmarshalling.
     * @throws BranchRuleTargetException
     */
    private function validateDocComponent(QtiComponent $docComponent): void
    {
        $branchRules = $docComponent->getComponentsByClassName('branchRule', true);

        if ($branchRules->count() === 0) {
            return;
        }

        $errors = [];
        $components = [];

        $outcomeDeclarationIds = array_map(
            static fn (OutcomeDeclaration $outcomeDeclaration) => $outcomeDeclaration->getIdentifier(),
            $docComponent->getComponentsByClassName('outcomeDeclaration')->getArrayCopy()
        );

        if (empty($outcomeDeclarationIds)) {
            $errors[] = 'Outcome Declarations are required for branch rules.';
        }

        /** @var BranchRule $branchRule */
        foreach ($branchRules as $branchRule) {
            $target = $branchRule->getTarget();

            if (empty($target)) {
                $errors[] = 'BranchRule is missing a target attribute';

                continue;
            }

            if (!empty($outcomeDeclarationIds)) {
                foreach ($branchRule->getExpression()->getExpressions() as $expression) {
                    if (
                        $expression instanceof Variable
                        && !in_array($expression->getIdentifier(), $outcomeDeclarationIds, true)
                    ) {
                        $errors[] = sprintf(
                            'Variable "%s" used in BranchRule targeting "%s" does not reference any existing outcome declaration.',
                            $expression->getIdentifier(),
                            $target
                        );
                    }
                }
            }

            if (in_array($target, BranchRule::RESERVED_TARGETS, true)) {
                continue;
            }

            $components[$target] ??= $docComponent->getComponentByIdentifier($target);

            if ($components[$target] === null) {
                $errors[] = sprintf('BranchRule target "%s" does not exist in the document', $target);

                continue;
            }

            $parentIdentifier = $branchRule->getParentIdentifier();

            if (!$parentIdentifier) {
                $errors[] = sprintf(
                    'BranchRule targeting "%s" does not have a parent or the parent does not contain an identifier',
                    $target
                );

                continue;
            }

            $components[$parentIdentifier] ??= $docComponent->getComponentByIdentifier($parentIdentifier);

            if ($components[$parentIdentifier] instanceof TestPart && !($components[$target] instanceof TestPart)) {
                $errors[] = sprintf(
                    'BranchRule inside test part "%s" must target another test part, but "%s" is not a test part',
                    $parentIdentifier,
                    $target
                );
            }
        }

        if (!empty($errors)) {
            throw new BranchRuleTargetException(implode('; ', $errors));
        }
    }
}
