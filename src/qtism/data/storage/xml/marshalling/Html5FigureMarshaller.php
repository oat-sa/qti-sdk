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
use Psr\Log\InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\data\content\xhtml\html5\Figure;
use qtism\data\QtiComponent;

class Html5FigureMarshaller extends Html5ElementMarshaller
{
    protected function marshall(QtiComponent $component): DOMElement
    {
        /** @var Figure $component */
        $element = parent::marshall($component);

        if ($component->hasId()) {
            $this->setDOMElementAttribute($element, 'id', $component->getId());
        }

        if ($component->hasClass()) {
            $this->setDOMElementAttribute($element, 'class', $component->getClass());
        }

        return $element;
    }

    /**
     * @return Figure
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        try {
            $id = $this->getDOMElementAttributeAs($element, 'id');
            $class = $this->getDOMElementAttributeAs($element, 'class');

            $component = new Figure($id, $class);
        } catch (InvalidArgumentException $exception) {
            throw UnmarshallingException::createFromInvalidArgumentException($element, $exception);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    public function getExpectedQtiClassName()
    {
        return Version::compare($this->getVersion(), '2.2', '>=') ? Figure::QTI_CLASS_NAME : 'not_existing';
    }
}