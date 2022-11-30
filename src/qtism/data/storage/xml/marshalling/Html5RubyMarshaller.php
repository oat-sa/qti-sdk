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

declare(strict_types=1);

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\content\FlowCollection;
use qtism\data\content\xhtml\html5\Ruby;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

class Html5RubyMarshaller extends Html5ContentMarshaller
{
    public function getExpectedQtiClassName(): string
    {
        return Ruby::QTI_CLASS_NAME;
    }

    /**
     *
     * @param QtiComponent&Ruby $component
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        $component = parent::unmarshallChildrenKnown($element, $children);

        if ($component->hasId()) {
            $this->setDOMElementAttribute($element, 'id', $component->getId());
        }

        if ($component->hasClass()) {
            $this->setDOMElementAttribute($element, 'class', $component->getClass());
        }

        return $component;
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = parent::marshallChildrenKnown($component, $elements);

        if ($component->hasId()) {
            $this->setDOMElementAttribute($element, 'id', $component->getId());
        }

        if ($component->hasClass()) {
            $this->setDOMElementAttribute($element, 'class', $component->getClass());
        }

        return $element;
    }

    protected static function getContentCollectionClassName()
    {
        return FlowCollection::class;
    }
}
