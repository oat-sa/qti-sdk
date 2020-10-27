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
use qtism\common\utils\Format;
use qtism\common\utils\Version;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\xhtml\html5\TrackKind;
use qtism\data\content\xhtml\Img;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for Html5 Track.
 */
class TrackMarshaller extends Html5ElementMarshaller
{
    /**
     * Marshall a Track object into a DOMElement object.
     *
     * @param QtiComponent $component A Track object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('track');

        $this->setDOMElementAttribute($element, 'src', $component->getSrc());

        if ($component->hasDefault()) {
            $this->setDOMElementAttribute($element, 'default', $component->getDefault() ? 'true' : 'false');
        }

        if ($component->hasKind()) {
            $this->setDOMElementAttribute($element, 'kind', TrackKind::getNameByConstant($component->getKind()));
        }

        if ($component->hasSrcLang()) {
            $this->setDOMElementAttribute($element, 'srclang', $component->getSrcLang());
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an HTML 5 track element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent n Img object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($src = $this->getDOMElementAttributeAs($element, 'src')) === null) {
            $msg = 'The required attribute "src" is missing from element "track".';
            throw new UnmarshallingException($msg, $element);
        }

        $component = new Track($src);

        $default = $this->getDOMElementAttributeAs($element, 'default');
        if ($default !== null) {
            $component->setDefault(Format::stringToBoolean($default));
        }

        $kind = $this->getDOMElementAttributeAs($element, 'kind');
        if ($kind !== null) {
            $component->setKind(TrackKind::getConstantByName($kind));
        }

        $srcLang = $this->getDOMElementAttributeAs($element, 'srclang');
        if ($srcLang !== null) {
            $component->setSrcLang($srcLang);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return Version::compare($this->getVersion(), '2.2', '>=') ? 'track' : 'not_existing';
    }
}
