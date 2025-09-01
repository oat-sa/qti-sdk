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

use qtism\data\AssessmentTest;
use qtism\data\QtiComponentIterator;
use qtism\data\storage\FileResolver;
use qtism\data\storage\LocalFileResolver;
use qtism\data\storage\xml\XmlStorageException;

/**
 * The InterpretationResolver class provides functionality to resolve interpretations
 * from outcome declarations to their corresponding definitions in the manifest.
 */
class InterpretationResolver
{
    /**
     * The manifest document containing interpretation definitions.
     *
     * @var ManifestDocument
     */
    private $manifestDocument;

    /**
     * The file resolver to use for resolving manifest files.
     *
     * @var FileResolver
     */
    private $fileResolver;

    /**
     * InterpretationResolver constructor.
     *
     * @param ManifestDocument|null $manifestDocument
     * @param FileResolver|null $fileResolver
     */
    public function __construct(?ManifestDocument $manifestDocument = null, ?FileResolver $fileResolver = null)
    {
        $this->manifestDocument = $manifestDocument;
        $this->fileResolver = $fileResolver;
    }

    /**
     * Load the manifest document from a file.
     *
     * @param string $manifestUrl The URL of the manifest file.
     * @param string $baseUrl The base URL for resolving relative paths.
     * @throws XmlStorageException If an error occurs while loading the manifest.
     */
    public function loadManifest(string $manifestUrl, string $baseUrl): void
    {
        if ($this->fileResolver === null) {
            $this->fileResolver = new LocalFileResolver($baseUrl);
        } else {
            $this->fileResolver->setBasePath($baseUrl);
        }

        $resolvedUrl = $this->fileResolver->resolve($manifestUrl);
        
        if ($this->manifestDocument === null) {
            $this->manifestDocument = new ManifestDocument();
        }
        
        $this->manifestDocument->setFilesystem($this->fileResolver->getFilesystem());
        $this->manifestDocument->load($resolvedUrl);
    }

    /**
     * Resolve all interpretations in an assessment test to their definitions.
     *
     * @param AssessmentTest $assessmentTest
     * @return array An array of resolved interpretations with their information.
     */
    public function resolveInterpretations(AssessmentTest $assessmentTest): array
    {
        if ($this->manifestDocument === null) {
            return [];
        }

        $resolvedInterpretations = [];
        $iterator = new QtiComponentIterator($assessmentTest, ['outcomeDeclaration']);

        foreach ($iterator as $outcomeDeclaration) {
            $interpretation = $outcomeDeclaration->getInterpretation();
            
            if (!empty($interpretation)) {
                $interpretationDef = $this->manifestDocument->getInterpretation($interpretation);
                if ($interpretationDef) {
                    $resolvedInterpretations[] = [
                        'outcomeIdentifier' => $outcomeDeclaration->getIdentifier(),
                        'interpretation' => $interpretation,
                        'interpretationDefinition' => $interpretationDef,
                        'outcomeDeclaration' => $outcomeDeclaration
                    ];
                }
            }
        }

        return $resolvedInterpretations;
    }

    /**
     * Get the interpretation value for a specific outcome declaration.
     *
     * @param string $outcomeIdentifier
     * @param float $value
     * @return string|null
     */
    public function getInterpretationValue(string $outcomeIdentifier, float $value): ?string
    {
        if ($this->manifestDocument === null) {
            return null;
        }

        // Find the outcome declaration with the given identifier
        $iterator = new QtiComponentIterator($this->getAssessmentTestContext(), ['outcomeDeclaration']);
        foreach ($iterator as $outcomeDeclaration) {
            if ($outcomeDeclaration->getIdentifier() === $outcomeIdentifier) {
                $interpretation = $outcomeDeclaration->getInterpretation();
                if (!empty($interpretation)) {
                    return $this->manifestDocument->getInterpretationValue($interpretation, $value);
                }
                break;
            }
        }

        return null;
    }

    /**
     * Get the interpretation information for a specific outcome declaration.
     *
     * @param string $outcomeIdentifier
     * @return array|null
     */
    public function getInterpretationInfo(string $outcomeIdentifier): ?array
    {
        if ($this->manifestDocument === null) {
            return null;
        }

        // Find the outcome declaration with the given identifier
        $iterator = new QtiComponentIterator($this->getAssessmentTestContext(), ['outcomeDeclaration']);
        foreach ($iterator as $outcomeDeclaration) {
            if ($outcomeDeclaration->getIdentifier() === $outcomeIdentifier) {
                $interpretation = $outcomeDeclaration->getInterpretation();
                if (!empty($interpretation)) {
                    return $this->manifestDocument->getInterpretation($interpretation);
                }
                break;
            }
        }

        return null;
    }

    /**
     * Get all available interpretations from the manifest.
     *
     * @return array
     */
    public function getAvailableInterpretations(): array
    {
        if ($this->manifestDocument === null) {
            return [];
        }

        return $this->manifestDocument->getInterpretations();
    }

    /**
     * Check if an interpretation exists for a given URI.
     *
     * @param string $interpretationUri
     * @return bool
     */
    public function hasInterpretation(string $interpretationUri): bool
    {
        if ($this->manifestDocument === null) {
            return false;
        }

        return $this->manifestDocument->hasInterpretation($interpretationUri);
    }

    /**
     * The assessment test context for resolving outcome identifiers.
     *
     * @var AssessmentTest|null
     */
    private $assessmentTestContext;

    /**
     * Set the assessment test context for resolving outcome identifiers.
     *
     * @param AssessmentTest $assessmentTest
     */
    public function setAssessmentTestContext(AssessmentTest $assessmentTest): void
    {
        $this->assessmentTestContext = $assessmentTest;
    }

    /**
     * Get the assessment test context.
     *
     * @return AssessmentTest|null
     */
    public function getAssessmentTestContext(): ?AssessmentTest
    {
        return $this->assessmentTestContext;
    }
} 