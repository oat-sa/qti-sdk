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

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\AssessmentSection;
use qtism\data\content\RubricBlockRefCollection;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * Marshalling implementation for the ExtendedAssessmentSection class.
 */
class ExtendedAssessmentSectionMarshaller extends AssessmentSectionMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\AssessmentSectionMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = parent::marshallChildrenKnown($component, $elements);

        foreach ($component->getRubricBlockRefs() as $rubricBlockRef) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($rubricBlockRef);
            $element->appendChild($marshaller->marshall($rubricBlockRef));
        }

        return $element;
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\AssessmentSectionMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children, AssessmentSection $assessmentSection = null)
    {
        $baseComponent = parent::unmarshallChildrenKnown($element, $children);
        $component = ExtendedAssessmentSection::createFromAssessmentSection($baseComponent);

        $rubricBlockRefElts = $this->getChildElementsByTagName($element, 'rubricBlockRef');
        if (count($rubricBlockRefElts) > 0) {
            $rubricBlockRefs = new RubricBlockRefCollection();

            foreach ($rubricBlockRefElts as $rubricBlockRefElt) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($rubricBlockRefElt);
                $rubricBlockRefs[] = $marshaller->unmarshall($rubricBlockRefElt);
            }

            $component->setRubricBlockRefs($rubricBlockRefs);
        }

        return $component;
    }
}
