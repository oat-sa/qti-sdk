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
use qtism\common\collections\IdentifierCollection;
use qtism\common\utils\Version;
use qtism\data\content\interactions\Gap;
use qtism\data\QtiComponent;
use qtism\data\ShowHide;

/**
 * Marshalling/Unmarshalling implementation for Gap.
 */
class GapMarshaller extends Marshaller
{
    /**
     * Marshall a Gap object into a DOMElement object.
     *
     * @param QtiComponent $component A Gap object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $version = $this->getVersion();
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        if ($component->isFixed() === true) {
            $this->setDOMElementAttribute($element, 'fixed', true);
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->hasTemplateIdentifier() === true) {
            $this->setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->getShowHide() === ShowHide::HIDE) {
            $this->setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant(ShowHide::HIDE));
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->isRequired() === true) {
            $this->setDOMElementAttribute($element, 'required', true);
        }

        if (Version::compare($version, '2.1.0', '<') === true) {
            $matchGroup = $component->getMatchGroup();
            if (count($matchGroup) > 0) {
                $this->setDOMElementAttribute($element, 'matchGroup', implode(' ', $matchGroup->getArrayCopy()));
            }
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an XHTML gap element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Gap object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $version = $this->getVersion();
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $component = new Gap($identifier);

            if (($fixed = $this->getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                $component->setFixed($fixed);
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($templateIdentifier = $this->getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
                $component->setTemplateIdentifier($templateIdentifier);
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                $component->setShowHide(ShowHide::getConstantByName($showHide));
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($required = $this->getDOMElementAttributeAs($element, 'required', 'boolean')) !== null) {
                $component->setRequired($required);
            }

            if (Version::compare($version, '2.1.0', '<') === true && ($matchGroup = $this->getDOMElementAttributeAs($element, 'matchGroup')) !== null) {
                $component->setMatchGroup(new IdentifierCollection(explode("\x20", $matchGroup)));
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from the 'gap' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'gap';
    }
}
