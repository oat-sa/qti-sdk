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

use qtism\data\QtiComponent;
use qtism\data\QtiComponentIterator;

/**
 * The InterpretationResolver class provides functionality to resolve interpretations
 * from outcome declarations to their corresponding definitions in the manifest.
 */
class InterpretationResolver
{
    private ?ManifestDocument $manifestDocument = null;
    private ?QtiComponent $assessmentTest = null;

    public function setAssessmentTest(QtiComponent $assessmentTest): void
    {
        $this->assessmentTest = $assessmentTest;
    }

    /**
     * @throws XmlStorageException
     */
    public function loadManifestDocument(string $manifestXmlString): void
    {
        $this->manifestDocument = new ManifestDocument();
        $this->manifestDocument->loadFromString($manifestXmlString);
    }

    public function resolveInterpretations(): array
    {
        if ($this->manifestDocument === null || $this->assessmentTest === null) {
            return [];
        }

        $resolvedInterpretations = [];
        $iterator = new QtiComponentIterator($this->assessmentTest, ['outcomeDeclaration']);

        foreach ($iterator as $outcomeDeclaration) {
            $interpretation = $outcomeDeclaration->getInterpretation();

            if (!empty($interpretation)) {
                $interpretationDef = $this->manifestDocument->getInterpretation($interpretation);
                if ($interpretationDef) {
                    $resolvedInterpretations[$outcomeDeclaration->getIdentifier()] = $interpretation;
                }
            }
        }

        return $resolvedInterpretations;
    }
}
