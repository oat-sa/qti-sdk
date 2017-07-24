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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\AdaptiveItemSelection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\SectionPartCollection;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for assessmentItemRef.
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 *
 */
class AdaptiveItemSelectionMarshaller extends RecursiveMarshaller
{
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $baseMarshaller = new SectionPartMarshaller($this->getVersion());
        $element = $baseMarshaller->marshall($component);

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children, AdaptiveItemSelection $selection = null)
    {
        $baseMarshaller = new SectionPartMarshaller($this->getVersion());
        $baseComponent = $baseMarshaller->unmarshall($element);
        if (empty($selection)) {
            $object = new AdaptiveItemSelection($baseComponent->getIdentifier());
        } else {
            $object = $selection;
        }

        // Deal with section parts... which are known :) !
        $object->setSectionParts($children);

        return $object;
    }

    /**
     *
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element)
    {
        if ($element->localName == 'adaptiveItemSelection') {
            $doc = $element->ownerDocument;
            $xpath = new \DOMXPath($doc);
            $nodeList = $xpath->query('adaptiveSettingsRef | adaptiveEngineRef | qtiUsagedataRef | qtiMetadataRef', $element);

            if ($nodeList->length == 0) {
                $xpath->registerNamespace('ais', $doc->lookupNamespaceURI($doc->namespaceURI));
                $nodeList = $xpath->query('ais:adaptiveSettingsRef | ais:adaptiveEngineRef | ais:qtiUsagedataRef | ais:qtiMetadataRef', $element);
            }
            $returnValue = array();

            for ($i = 0; $i < $nodeList->length; $i++) {
                $returnValue[] = $nodeList->item($i);
            }

            return $returnValue;
        } else {
            return array();
        }
    }

    protected function isElementFinal(\DOMNode $element)
    {
        return $element->localName != 'adaptiveItemSelection';
    }

    protected function isComponentFinal(QtiComponent $component)
    {
        return !$component instanceof AdaptiveItemSelection;
    }

    protected function getChildrenComponents(QtiComponent $component)
    {
        if ($component instanceof AdaptiveItemSelection) {
            return $component->getSectionParts()->getArrayCopy();
        } else {
            return array();
        }
    }

    protected function createCollection(DOMElement $currentNode)
    {
        return new SectionPartCollection();
    }

    public function getExpectedQtiClassName()
    {
        return 'adaptiveItemSelection';
    }
}
