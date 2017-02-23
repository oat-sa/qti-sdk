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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\ShowHide;
use qtism\data\content\InlineStaticCollection;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * The Marshaller implementation for Hottext elements of the content model.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class HottextMarshaller extends ContentMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {

            $fqClass = $this->lookupClass($element);
            $component = new $fqClass($identifier);

            if (($fixed = $this->getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                $component->setFixed($fixed);
            }

            if (($templateIdentifier = $this->getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
                $component->setTemplateIdentifier($templateIdentifier);
            }

            if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                $component->setShowHide(ShowHide::getConstantByName($showHide));
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $component->setContent(new InlineStaticCollection($children->getArrayCopy()));
            $this->fillBodyElement($component, $element);

            return $component;

        } else {
            $msg = "The mandatory 'identifier' attribute is missing from the 'hottext' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->fillElement($element, $component);

        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        if ($component->isFixed() === true) {
            self::setDOMElementAttribute($element, 'fixed', true);
        }

        if ($component->hasTemplateIdentifier() === true) {
            self::setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if ($component->getShowHide() !== ShowHide::SHOW) {
            self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant(ShowHide::HIDE));
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
        $this->lookupClasses = array("qtism\\data\\content\\interactions");
    }
}
