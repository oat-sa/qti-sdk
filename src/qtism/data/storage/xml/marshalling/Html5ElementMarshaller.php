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
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\common\utils\Version;
use qtism\data\content\BodyElement;
use qtism\data\content\enums\Role;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\trait\QtiHtml5AttributeTrait;
use qtism\data\storage\xml\marshalling\trait\QtiNamespacePrefixTrait;
use qtism\data\storage\xml\versions\QtiVersion;

/**
 * Marshalling/Unmarshalling implementation for generic Html5.
 */
abstract class Html5ElementMarshaller extends Marshaller
{
    use QtiNamespacePrefixTrait;
    use QtiHtml5AttributeTrait;

    /**
     * Marshall a Html5 element object into a DOMElement object.
     *
     * @param QtiComponent $component
     * @return DOMElement The according DOMElement object.
     * @throws \DOMException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        /** @var Html5Element $component */

        $element = $this->getNamespacedElement($component);
        $this->fillElement($element, $component);

        return $this->marshallHtml5Attributes($component, $element);
    }

    /**
     * Fill $bodyElement with the following Html 5 element attributes:
     *
     * * title
     * * role
     *
     * @param BodyElement $bodyElement The bodyElement to fill.
     * @param DOMElement $element The DOMElement object from where the attribute values must be retrieved.
     * @throws UnmarshallingException If one of the attributes of $element is not valid.
     */
    protected function fillBodyElement(BodyElement $bodyElement, DOMElement $element)
    {
        $this->fillBodyElementAttributes($bodyElement, $element);

        parent::fillBodyElement($bodyElement, $element);
    }
}
