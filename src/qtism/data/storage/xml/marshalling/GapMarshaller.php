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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\common\collections\IdentifierCollection;
use qtism\common\utils\Version;
use qtism\data\ShowHide;
use qtism\data\content\interactions\Gap;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for Gap.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class GapMarshaller extends Marshaller
{
    /**
	 * Marshall a Gap object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A Gap object.
	 * @return \DOMElement The according DOMElement object.
	 * @throws \qtism\data\storage\xml\marshalling\MarshallingException
	 */
    protected function marshall(QtiComponent $component)
    {
        $version = $this->getVersion();
        $element = self::getDOMCradle()->createElement('gap');
        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        if ($component->isFixed() === true) {
            self::setDOMElementAttribute($element, 'fixed' , true);
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->hasTemplateIdentifier() === true) {
            self::setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->getShowHide() === ShowHide::HIDE) {
            self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant(ShowHide::HIDE));
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->isRequired() === true) {
            self::setDOMElementAttribute($element, 'required', true);
        }
        
        if (Version::compare($version, '2.1.0', '<') === true) {
            $matchGroup = $component->getMatchGroup();
            if (count($matchGroup) > 0) {
                self::setDOMElementAttribute($element, 'matchGroup', implode(' ', $matchGroup->getArrayCopy()));
            }
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to an XHTML gap element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\QtiComponent A Gap object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException
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
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return 'gap';
    }
}
