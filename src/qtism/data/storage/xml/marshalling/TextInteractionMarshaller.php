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

use qtism\common\utils\Version;
use qtism\data\content\interactions\TextFormat;
use qtism\data\content\interactions\ExtendedTextInteraction;
use qtism\data\content\interactions\TextEntryInteraction;
use qtism\data\QtiComponent;
use \InvalidArgumentException;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for TextEntryInteraction/ExtendedTextInteraction.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TextInteractionMarshaller extends Marshaller
{
    /**
	 * Marshall a TextEntryInteraction/ExtendedTextInteraction object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A TextEntryInteraction/ExtendedTextInteraction object.
	 * @return \DOMElement The according DOMElement object.
	 * @throws \qtism\data\storage\xml\marshalling\MarshallingException
	 */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $version = $this->getVersion();

        self::setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        if ($component->getBase() !== 10) {
            self::setDOMElementAttribute($element, 'base', $component->getBase());
        }

        if ($component->hasStringIdentifier() === true) {
            self::setDOMElementAttribute($element, 'stringIdentifier', $component->getStringIdentifier());
        }

        if ($component->hasExpectedLength() === true) {
            self::setDOMElementAttribute($element, 'expectedLength', $component->getExpectedLength());
        }

        if ($component->hasPatternMask() === true) {
            self::setDOMElementAttribute($element, 'patternMask', $component->getPatternMask());
        }

        if ($component->hasPlaceholderText() === true) {
            self::setDOMElementAttribute($element, 'placeholderText', $component->getPlaceholderText());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->setXmlBase());
        }

        if ($element->localName === 'extendedTextInteraction') {
            if ($component->hasMaxStrings() === true) {
                self::setDOMElementAttribute($element, 'maxStrings', $component->getMaxStrings());
            }

            if (Version::compare($version, '2.1.0', '>=') === true && $component->getMinStrings() !== 0) {
                self::setDOMElementAttribute($element, 'minStrings', $component->getMinStrings());
            }

            if ($component->hasExpectedLines() === true) {
                self::setDOMElementAttribute($element, 'expectedLines', $component->getExpectedLines());
            }

            if (Version::compare($version, '2.1.0', '>=') === true && $component->getFormat() !== TextFormat::PLAIN) {
                self::setDOMElementAttribute($element, 'format', TextFormat::getNameByConstant($component->getFormat()));
            }

            if ($component->hasPrompt() === true) {
                $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
            }
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to a textEntryInteraction/extendedTextInteraction element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\QtiComponent A TextEntryInteraction/ExtendedTextInteraction object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException
	 */
    protected function unmarshall(DOMElement $element)
    {
        $version = $this->getVersion();
        
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {

            try {
                $class = 'qtism\\data\\content\\interactions\\' . ucfirst($element->localName);
                $component = new $class($responseIdentifier);
            } catch (InvalidArgumentException $e) {
                $msg = "The value '${responseIdentifier}' of the 'responseIdentifier' attribute of the '" . $element->localName . "' element is not a valid identifier.";
                throw new UnmarshallingException($msg, $element, $e);
            }

            if (($base = $this->getDOMElementAttributeAs($element, 'base', 'integer')) !== null) {
                $component->setBase($base);
            }

            if (($stringIdentifier = $this->getDOMElementAttributeAs($element, 'stringIdentifier')) !== null) {
                $component->setStringIdentifier($stringIdentifier);
            }

            if (($expectedLength = $this->getDOMElementAttributeAs($element, 'expectedLength', 'integer')) !== null) {
                $component->setExpectedLength($expectedLength);
            }

            if (($patternMask = $this->getDOMElementAttributeAs($element, 'patternMask')) !== null) {
                $component->setPatternMask($patternMask);
            }

            if (($placeholderText = $this->getDOMElementAttributeAs($element, 'placeholderText')) !== null) {
                $component->setPlaceholderText($placeholderText);
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            if ($element->localName === 'extendedTextInteraction') {

                if (($maxStrings = $this->getDOMElementAttributeAs($element, 'maxStrings', 'integer')) !== null) {
                    $component->setMaxStrings($maxStrings);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($minStrings = $this->getDOMElementAttributeAs($element, 'minStrings', 'integer')) !== null) {
                    $component->setMinStrings($minStrings);
                }

                if (($expectedLines = $this->getDOMElementAttributeAs($element, 'expectedLines', 'integer')) !== null) {
                    $component->setExpectedLines($expectedLines);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($format = $this->getDOMElementAttributeAs($element, 'format')) !== null) {
                    $component->setFormat(TextFormat::getConstantByName($format));
                }

                $promptElts = self::getChildElementsByTagName($element, 'prompt');
                if (count($promptElts) > 0) {
                    $component->setPrompt($this->getMarshallerFactory()->createMarshaller($promptElts[0])->unmarshall($promptElts[0]));
                }
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the '" . $element->localName . "' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return '';
    }
}
