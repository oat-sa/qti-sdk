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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\common\utils\Version;
use qtism\data\content\FlowCollection;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * Marshalling/Unmarshalling implementation for generic Html5.
 */
abstract class Html5ContentMarshaller extends ContentMarshaller
{
    use QtiNamespacePrefixTrait;
    use QtiHtml5AttributeTrait;

    public function getExpectedQtiClassName()
    {
        return Version::compare($this->getVersion(), '2.2', '>=') ? static::getExpectedQtiClassName() : 'not_existing';
    }

    abstract protected static function getContentCollectionClassName();

    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $fqClass = $this->lookupClass($element);
        $component = new $fqClass();
        $collectionClassName = static::getContentCollectionClassName() ?? FlowCollection::class;

        $component->setContent(new $collectionClassName($children->getArrayCopy()));

        if (($xmlBase = self::getXmlBase($element)) !== false) {
            $component->setXmlBase($xmlBase);
        }

        $this->fillBodyElementAttributes($component, $element);
        $this->fillBodyElement($component, $element);

        return $component;
    }


    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        /** @var Html5Element $component */
        $element = $this->getNamespacedElement($component);

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        $this->fillElement($element, $component);

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = [
            "qtism\\data\\content\\xhtml",
            "qtism\\data\\content\\xhtml\\html5"
        ];
    }
}
