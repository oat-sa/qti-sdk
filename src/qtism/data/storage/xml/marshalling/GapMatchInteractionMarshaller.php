<?php

declare(strict_types=1);

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
use qtism\data\content\BlockStaticCollection;
use qtism\data\content\interactions\GapChoiceCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for GapMatchInteraction elements of the content model.
 */
class GapMatchInteractionMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $gapChoiceElts = $this->getChildElementsByTagName($element, ['gapText', 'gapImg']);
            if (count($gapChoiceElts) > 0) {
                $gapChoices = new GapChoiceCollection();
                foreach ($gapChoiceElts as $g) {
                    $gapChoices[] = $this->getMarshallerFactory()->createMarshaller($g)->unmarshall($g);
                }

                $fqClass = $this->lookupClass($element);
                $component = new $fqClass($responseIdentifier, $gapChoices, new BlockStaticCollection($children->getArrayCopy()));

                $promptElts = $this->getChildElementsByTagName($element, 'prompt');
                if (count($promptElts) === 1) {
                    $component->setPrompt($this->getMarshallerFactory()->createMarshaller($promptElts[0])->unmarshall($promptElts[0]));
                }

                if (($shuffle = $this->getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
                    $component->setShuffle($shuffle);
                }

                if (($xmlBase = self::getXmlBase($element)) !== false) {
                    $component->setXmlBase($xmlBase);
                }

                $this->fillBodyElement($component, $element);

                return $component;
            } else {
                $msg = "A 'gapMatchInteraction' element must contain at least 1 'gapChoice' element, none given.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'gapMatchInteraction' element.";
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
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $version = $this->getVersion();
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        if ($component->mustShuffle() === true || Version::compare($version, '2.0.0', '==') === true) {
            $this->setDOMElementAttribute($element, 'shuffle', $component->mustShuffle());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        foreach ($component->getGapChoices() as $g) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($g)->marshall($g));
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses(): void
    {
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
