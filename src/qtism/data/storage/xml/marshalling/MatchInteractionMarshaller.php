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
use InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\data\content\interactions\SimpleMatchSetCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for MatchInteraction elements of the content model.
 */
class MatchInteractionMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $version = $this->getVersion();

        // responseIdentifier.
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $fqClass = $this->lookupClass($element);

            try {
                $component = new $fqClass($responseIdentifier, new SimpleMatchSetCollection($children->getArrayCopy()));
            } catch (InvalidArgumentException $e) {
                $msg = 'A matchInteraction element must contain exactly 2 simpleMatchSet elements, ' . count($children) . "' given.";
                throw new UnmarshallingException($msg, $element, $e);
            }

            // shuffle.
            if (($shuffle = $this->getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
                $component->setShuffle($shuffle);
            } elseif (Version::compare($version, '2.1.0', '<') === true) {
                $msg = "The mandatory attribute 'shuffle' is missing from the 'matchInteraction' element.";
                throw new UnmarshallingException($msg, $element);
            }

            // maxAssociations.
            if (($maxAssociations = $this->getDOMElementAttributeAs($element, 'maxAssociations', 'integer')) !== null) {
                $component->setMaxAssociations($maxAssociations);
            } elseif (Version::compare($version, '2.1.0', '<') === true) {
                $msg = "The mandatory attribute 'maxAssociations' is missing from the 'matchInteraction' element.";
                throw new UnmarshallingException($msg, $element);
            }

            // minAssociations.
            if (Version::compare($version, '2.1.0', '>=') === true && ($minAssociations = $this->getDOMElementAttributeAs($element, 'minAssociations', 'integer')) !== null) {
                $component->setMinAssociations($minAssociations);
            }

            // xml:base
            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            // prompt.
            $promptElts = $this->getChildElementsByTagName($element, 'prompt');
            if (count($promptElts) > 0) {
                $promptElt = $promptElts[0];
                $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
                $component->setPrompt($prompt);
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'matchInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     * @throws MarshallingException
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $version = $this->getVersion();
        $element = $this->createElement($component);

        $this->fillElement($element, $component);

        // responseIdentifier.
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        // prompt.
        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        // shuffle.
        if ((Version::compare($version, '2.1.0', '>=') === true && $component->mustShuffle() !== false) || Version::compare($version, '2.1.0', '<') === true) {
            $this->setDOMElementAttribute($element, 'shuffle', $component->mustShuffle());
        }

        // maxAssociations.
        if ($component->getMaxAssociations() !== 1 || Version::compare($version, '2.1.0', '<') === true) {
            $this->setDOMElementAttribute($element, 'maxAssociations', $component->getMaxAssociations());
        }

        // minAssociations.
        if ($component->getMinAssociations() !== 0 && Version::compare($version, '2.1.0', '>=') === true) {
            $this->setDOMElementAttribute($element, 'minAssociations', $component->getMinAssociations());
        }

        // xml:base
        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\ContentMarshaller::setLookupClasses()
     */
    protected function setLookupClasses()
    {
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
