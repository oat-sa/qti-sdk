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
use InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\data\content\xhtml\html5\Audio;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for Html5 Audio.
 */
class AudioMarshaller extends Html5MediaMarshaller
{
    /**
     * Unmarshall a DOMElement object corresponding to an HTML 5 audio element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent An Audio object.
     * @throws UnmarshallingException
     * @throws MarshallerNotFoundException
     */
    protected function unmarshall(DOMElement $element)
    {
        try {
            $component = new Audio();
        } catch (InvalidArgumentException $exception) {
            throw UnmarshallingException::createFromInvalidArgumentException($element, $exception);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return Version::compare($this->getVersion(), '2.2', '>=') ? 'audio' : 'not_existing';
    }
}
