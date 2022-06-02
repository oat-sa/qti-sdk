<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\common\utils\Version;
use qtism\data\content\BodyElement;
use qtism\data\content\enums\Role;
use qtism\data\content\FlowCollection;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\xml\versions\QtiVersion;

/**
 * The Marshaller implementation for object elements of the content model.
 */
class Html5ContentMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        $fqClass = $this->lookupClass($element);
        $component = new $fqClass();
        $component->setContent(new FlowCollection($children->getArrayCopy()));

        if (($xmlBase = self::getXmlBase($element)) !== false) {
            $component->setXmlBase($xmlBase);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        /** @var Html5Element $component */
        $prefix = $component->getTargetNamespacePrefix();
        $version = QtiVersion::create($this->getVersion());
        $namespace = $version->getExternalNamespace($prefix);

        $element = static::getDOMCradle()->createElementNS(
            $namespace,
            $prefix . ':' . $component->getQtiClassName()
        );

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        $this->fillElement($element, $component);

        if ($component->hasTitle()) {
            $this->setDOMElementAttribute($element, 'title', $component->getTitle());
        }

        if ($component->hasRole()) {
            $this->setDOMElementAttribute($element, 'role', Role::getNameByConstant($component->getRole()));
        }

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = [
            "qtism\\data\\content\\xhtml",
            "qtism\\data\\content\\xhtml\\html5",
            "qtism\\data\\content\\xhtml\\text",
        ];
    }

    protected function fillBodyElement(BodyElement $bodyElement, DOMElement $element): void {
        if (Version::compare($this->getVersion(), '2.2.0', '>=') === true) {
            $title = $this->getDOMElementAttributeAs($element, 'title');
            $bodyElement->setTitle($title);

            $role = $this->getDOMElementAttributeAs($element, 'role', Role::class);
            $bodyElement->setRole($role);
        }

        parent::fillBodyElement($bodyElement, $element);
    }
}
