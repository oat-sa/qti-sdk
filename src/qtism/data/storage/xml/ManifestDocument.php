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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Janos Pribelszki <janos.pribelszki@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use qtism\data\storage\StorageException;

/**
 * The ManifestDocument class represents an IMS Content Package manifest file.
 * It provides functionality to parse the manifest and extract interpretation definitions
 * from the metadata section.
 */
class ManifestDocument
{
    private DOMDocument $domDocument;

    /**
     * The extracted interpretation definitions.
     *
     * @var array
     */
    private array $interpretations = [];

    public function __construct()
    {
        $this->domDocument = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @throws XmlStorageException
     */
    public function loadFromString(string $xmlString): void
    {
        try {
            $this->domDocument->loadXML($xmlString);
            $this->extractInterpretations();
        } catch (Exception $e) {
            throw new XmlStorageException(
                'An error occurred while parsing the manifest content.',
                StorageException::READ,
                $e
            );
        }
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

    private function extractInterpretations(): void
    {
        $this->interpretations = [];

        // Look for LOM metadata with custom properties.
        $xpath = new DOMXPath($this->domDocument);
        $xpath->registerNamespace('imsmd', 'http://ltsc.ieee.org/xsd/LOM');
        $xpath->registerNamespace('imscp', 'http://www.imsglobal.org/xsd/imscp_v1p1');

        // Find all custom properties in the metadata.
        $properties = $xpath->query('//imsmd:lom/imsmd:metaMetadata/imscp:extension/imscp:customProperties/imscp:property');
        foreach ($properties as $property) {
            $this->extractInterpretationFromProperty($property);
        }
    }

    private function extractInterpretationFromProperty(DOMElement $property): void
    {
        $uri   = $this->getElementText($property, 'imscp:uri');
        $label = $this->getElementText($property, 'imscp:label');
        $domain= $this->getElementText($property, 'imscp:domain');
        $interpretationData = $this->getElementText($property, 'imscp:scale');

        // Check if this property represents an interpretation.
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
                // Skip invalid interpretation data.
            }
        }
    }

    private function getElementText(DOMElement $parent, string $childQName): ?string
    {
        $xpath = new DOMXPath($parent->ownerDocument);
        $xpath->registerNamespace('imsmd', 'http://ltsc.ieee.org/xsd/LOM');
        $xpath->registerNamespace('imscp', 'http://www.imsglobal.org/xsd/imscp_v1p1');

        $node = $xpath->query('./' . $childQName, $parent)->item(0);

        return $node ? trim($node->textContent) : null;
    }
}
