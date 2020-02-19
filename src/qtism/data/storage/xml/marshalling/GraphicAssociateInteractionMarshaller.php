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
use qtism\common\utils\Version;
use qtism\data\content\interactions\AssociableHotspotCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for GraphicAssociateInteraction elements of the content model.
 */
class GraphicAssociateInteractionMarshaller extends ContentMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $version = $this->getVersion();
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $objectElts = $this->getChildElementsByTagName($element, 'object');
            if (count($objectElts) > 0) {
                $object = $this->getMarshallerFactory()->createMarshaller($objectElts[0])->unmarshall($objectElts[0]);
                $choices = new AssociableHotspotCollection($children->getArrayCopy());

                if (count($choices) > 0) {
                    $fqClass = $this->lookupClass($element);
                    $component = new $fqClass($responseIdentifier, $object, $choices);

                    if (($maxAssociations = $this->getDOMElementAttributeAs($element, 'maxAssociations', 'integer')) !== null) {
                        $component->setMaxAssociations($maxAssociations);
                    } elseif (Version::compare($version, '2.1.0', '<') === true) {
                        $msg = "The mandatory 'maxAssociations' attribute is missing from the 'graphicAssociateInteraction' element.";
                        throw new UnmarshallingException($msg, $element);
                    }

                    if (Version::compare($version, '2.1.0', '>=') === true && ($minAssociations = $this->getDOMElementAttributeAs($element, 'minAssociations', 'integer')) !== null) {
                        $component->setMinAssociations($minAssociations);
                    }

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
                    $msg = "A 'graphicAssociateInteraction' element must contain at lease one 'associableHotspot' element, none given.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "A 'graphicAssociateInteraction' element must contain exactly one 'object' element, none given.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'graphicAssociateInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $version = $this->getVersion();
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getObject())->marshall($component->getObject()));

        if (Version::compare($version, '2.1.0', '>=') === true && $component->getMinAssociations() !== 0) {
            $this->setDOMElementAttribute($element, 'minAssociations', $component->getMinAssociations());
        }

        if (Version::compare($version, '2.1.0', '<') === true || (Version::compare($version, '2.1.0', '>=') && $component->getMaxAssociations() !== 1)) {
            $this->setDOMElementAttribute($element, 'maxAssociations', $component->getMaxAssociations());
        }

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
