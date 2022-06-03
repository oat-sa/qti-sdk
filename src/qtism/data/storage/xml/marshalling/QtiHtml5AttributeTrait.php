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

use qtism\common\utils\Version;
use qtism\data\content\BodyElement;
use qtism\data\content\enums\Role;
use qtism\data\QtiComponent;
use DOMElement;

trait QtiHtml5AttributeTrait
{
    abstract function setDOMElementAttribute(DOMElement $element, $attribute, $value);
    abstract function getDOMElementAttributeAs(DOMElement $element, $attribute, $datatype = 'string');

    /**
     * @return DOMElement
     */
    public function marshallHtml5Attributes(QtiComponent $component, DOMElement $element)
    {
        if ($component->hasTitle()) {
            $this->setDOMElementAttribute($element, 'title', $component->getTitle());
        }

        if ($component->hasRole()) {
            $this->setDOMElementAttribute($element, 'role', Role::getNameByConstant($component->getRole()));
        }

        return $element;
    }

    protected function fillBodyElementAttributes(BodyElement &$bodyElement, DOMElement $element)
    {
        if (Version::compare($this->getVersion(), '2.2.0', '>=') === true) {
            $title = $this->getDOMElementAttributeAs($element, 'title');
            $bodyElement->setTitle($title);

            $role = $this->getDOMElementAttributeAs($element, 'role', Role::class);
            $bodyElement->setRole($role);
        }
    }
}
