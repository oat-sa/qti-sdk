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
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for AssociateInteraction elements of the content model.
 */
class AssociateInteractionMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $fqClass = $this->lookupClass($element);
            $component = new $fqClass($responseIdentifier, new SimpleAssociableChoiceCollection($children->getArrayCopy()));

            // shuffle.
            if (($shuffle = $this->getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
                $component->setShuffle($shuffle);
            }

            // maxAssociations.
            if (($maxAssociations = $this->getDOMElementAttributeAs($element, 'maxAssociations', 'integer')) !== null) {
                $component->setMaxAssociations($maxAssociations);
            }

            // minAssociations.
            if (($minAssociations = $this->getDOMElementAttributeAs($element, 'minAssociations', 'integer')) !== null) {
                $component->setMinAssociations($minAssociations);
            }

            // xml:base.
            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $promptElts = $this->getChildElementsByTagName($element, 'prompt');
            if (count($promptElts) > 0) {
                $promptElt = $promptElts[0];
                $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
                $component->setPrompt($prompt);
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'associateInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = $this->createElement($component);
        $this->fillElement($element, $component);

        // responseIdentifier.
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        // prompt.
        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        // shuffle.
        if ($component->mustShuffle() !== false) {
            $this->setDOMElementAttribute($element, 'shuffle', true);
        }

        // maxAssociations.
        if ($component->getMaxAssociations() !== 1) {
            $this->setDOMElementAttribute($element, 'maxAssociations', $component->getMaxAssociations());
        }

        // minAssociations.
        if ($component->getMinAssociations() !== 0) {
            $this->setDOMElementAttribute($element, 'minAssociations', $component->getMinAssociations());
        }

        // xml:base.
        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
