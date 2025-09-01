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
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use DOMDocument;
use DOMElement;
use Exception;
use qtism\data\storage\xml\filesystem\Filesystem;
use qtism\data\storage\xml\XmlStorageException;

/**
 * The ManifestDocument class represents an IMS Content Package manifest file.
 * It provides functionality to parse the manifest and extract interpretation definitions
 * from the metadata section.
 */
class ManifestDocument
{
    /**
     * The DOM document representing the manifest.
     *
     * @var DOMDocument
     */
    private $domDocument;

    /**
     * The filesystem to use for file operations.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * The URL of the manifest file.
     *
     * @var string
     */
    private $url;

    /**
     * The extracted interpretation definitions.
     *
     * @var array
     */
    private $interpretations = [];

    /**
     * ManifestDocument constructor.
     *
     * @param Filesystem|null $filesystem
     */
    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Set the filesystem to use for file operations.
     *
     * @param Filesystem|null $filesystem
     */
    public function setFilesystem(?Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get the filesystem used for file operations.
     *
     * @return Filesystem|null
     */
    public function getFilesystem(): ?Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set the URL of the manifest file.
     *
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Get the URL of the manifest file.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Load the manifest from a file.
     *
     * @param string $url The URL of the manifest file.
     * @throws XmlStorageException If an error occurs while loading the manifest.
     */
    public function load(string $url): void
    {
        $this->setUrl($url);

        try {
            if ($this->filesystem !== null) {
                $content = $this->filesystem->read($url);
            } else {
                $content = file_get_contents($url);
                if ($content === false) {
                    throw new XmlStorageException("Could not read manifest file: {$url}");
                }
            }

            $this->loadFromString($content);
        } catch (Exception $e) {
            $msg = "An error occurred while loading the manifest file: {$url}";
            throw new XmlStorageException($msg, XmlStorageException::READ, $e);
        }
    }

    /**
     * Load the manifest from a string.
     *
     * @param string $content The manifest content as a string.
     * @throws XmlStorageException If an error occurs while parsing the content.
     */
    public function loadFromString(string $content): void
    {
        try {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->preserveWhiteSpace = true;
            $doc->loadXML($content);
            $this->setDomDocument($doc);
            $this->extractInterpretations();
        } catch (Exception $e) {
            $msg = 'An error occurred while parsing the manifest content.';
            throw new XmlStorageException($msg, XmlStorageException::READ, $e);
        }
    }

    /**
     * Set the DOM document.
     *
     * @param DOMDocument $domDocument
     */
    protected function setDomDocument(DOMDocument $domDocument): void
    {
        $this->domDocument = $domDocument;
    }

    /**
     * Get the DOM document.
     *
     * @return DOMDocument
     */
    public function getDomDocument(): DOMDocument
    {
        return $this->domDocument;
    }

    /**
     * Extract interpretation definitions from the manifest metadata.
     */
    protected function extractInterpretations(): void
    {
        $this->interpretations = [];
        
        // Look for LOM metadata with custom properties
        $xpath = new \DOMXPath($this->domDocument);
        $xpath->registerNamespace('imsmd', 'http://ltsc.ieee.org/xsd/LOM');
        
        // Find all custom properties in the metadata
        $properties = $xpath->query('//imsmd:customProperties/imsmd:property');
        
        foreach ($properties as $property) {
            $this->extractInterpretationFromProperty($property);
        }
    }

    /**
     * Extract interpretation information from a custom property element.
     *
     * @param DOMElement $property
     */
    protected function extractInterpretationFromProperty(DOMElement $property): void
    {
        $uri = $this->getElementText($property, 'imsmd:uri');
        $label = $this->getElementText($property, 'imsmd:label');
        $domain = $this->getElementText($property, 'imsmd:domain');
        $interpretationData = $this->getElementText($property, 'imsmd:scale');
        
        // Check if this property represents an interpretation
        if ($uri && $interpretationData) {
            try {
                $data = json_decode($interpretationData, true);
                if (is_array($data)) {
                    $this->interpretations[$uri] = [
                        'uri' => $uri,
                        'label' => $label,
                        'domain' => $domain,
                        'interpretationData' => $data
                    ];
                }
            } catch (Exception $e) {
                // Skip invalid interpretation data
            }
        }
    }

    /**
     * Get the text content of a child element.
     *
     * @param DOMElement $parent
     * @param string $childName
     * @return string|null
     */
    protected function getElementText(DOMElement $parent, string $childName): ?string
    {
        $xpath = new \DOMXPath($parent->ownerDocument);
        $xpath->registerNamespace('imsmd', 'http://ltsc.ieee.org/xsd/LOM');
        
        $element = $xpath->query($childName, $parent)->item(0);
        return $element ? trim($element->textContent) : null;
    }

    /**
     * Get all extracted interpretation definitions.
     *
     * @return array
     */
    public function getInterpretations(): array
    {
        return $this->interpretations;
    }

    /**
     * Get a specific interpretation by URI.
     *
     * @param string $uri
     * @return array|null
     */
    public function getInterpretation(string $uri): ?array
    {
        return $this->interpretations[$uri] ?? null;
    }

    /**
     * Check if an interpretation with the given URI exists.
     *
     * @param string $uri
     * @return bool
     */
    public function hasInterpretation(string $uri): bool
    {
        return isset($this->interpretations[$uri]);
    }

    /**
     * Get the interpretation value for a given interpretation URI and numeric value.
     *
     * @param string $interpretationUri
     * @param float $value
     * @return string|null
     */
    public function getInterpretationValue(string $interpretationUri, float $value): ?string
    {
        $interpretation = $this->getInterpretation($interpretationUri);
        if (!$interpretation || !isset($interpretation['interpretationData'])) {
            return null;
        }

        $interpretationData = $interpretation['interpretationData'];
        $intValue = (int)$value;
        
        return $interpretationData[(string)$intValue] ?? null;
    }
} 