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
use qtism\data\content\interactions\InlineChoiceCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for InlineChoiceInteraction elements of the content model.
 */
class InlineChoiceInteractionMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $version = $this->getVersion();
            $fqClass = $this->lookupClass($element);

            $choices = new InlineChoiceCollection($children->getArrayCopy());
            if (count($choices) === 0) {
                $msg = "An 'inlineChoiceInteraction' element must contain at least 1 'inlineChoice' elements, none given.";
                throw new UnmarshallingException($msg, $element);
            }

            try {
                $component = new $fqClass($responseIdentifier, $choices);
            } catch (InvalidArgumentException $e) {
                $msg = "The value of the attribute 'responseIdentifier' for element 'inlineChoiceInteraction' is not a valid identifier.";
                throw new UnmarshallingException($msg, $element, $e);
            }

            if (($shuffle = $this->getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
                $component->setShuffle($shuffle);
            } elseif (Version::compare($version, '2.1.0', '<') === true) {
                $msg = "The mandatory 'shuffle' attribute is missing from the 'inlineChoiceInteraction' element.";
                throw new UnmarshallingException($msg, $element);
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($required = $this->getDOMElementAttributeAs($element, 'required', 'boolean')) !== null) {
                $component->setRequired($required);
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'inlineChoiceInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $version = $this->getVersion();
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        if ($component->mustShuffle() !== false || Version::compare($version, '2.0.0', '==') === true) {
            $this->setDOMElementAttribute($element, 'shuffle', $component->mustShuffle());
        }

        if (Version::compare($version, '2.1.0', '>=') && $component->isRequired() !== false) {
            $this->setDOMElementAttribute($element, 'required', $component->isRequired());
        }

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
