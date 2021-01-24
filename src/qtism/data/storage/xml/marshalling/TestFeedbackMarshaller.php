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
use DOMText;
use InvalidArgumentException;
use qtism\data\content\FlowStatic;
use qtism\data\content\FlowStaticCollection;
use qtism\data\QtiComponent;
use qtism\data\ShowHide;
use qtism\data\TestFeedback;
use qtism\data\TestFeedbackAccess;

/**
 * Marshalling/Unmarshalling implementation for TestFeedback.
 */
class TestFeedbackMarshaller extends Marshaller
{
    /**
     * Marshall a TestFeedback object into a DOMElement object.
     *
     * @param QtiComponent $component A TestFeedback object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());
        $access = ($component->getAccess() == TestFeedbackAccess::AT_END) ? 'atEnd' : 'during';
        $showHide = ($component->getShowHide() == ShowHide::SHOW) ? 'show' : 'hide';

        $this->setDOMElementAttribute($element, 'access', $access);
        $this->setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        $this->setDOMElementAttribute($element, 'showHide', $showHide);
        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        $title = $component->getTitle();
        if (!empty($title)) {
            $this->setDOMElementAttribute($element, 'title', $title);
        }

        foreach ($component->getContent() as $flowStatic) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($flowStatic);
            $element->appendChild($marshaller->marshall($flowStatic));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI testFeedback element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A TestFeedback object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
            if (($outcomeIdentifier = $this->getDOMElementAttributeAs($element, 'outcomeIdentifier', 'string')) !== null) {
                if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide', 'string')) !== null) {
                    if (($access = $this->getDOMElementAttributeAs($element, 'access', 'string')) !== null) {
                        $content = new FlowStaticCollection();

                        foreach (self::getChildElements($element, true) as $elt) {
                            if ($elt instanceof DOMText) {
                                $elt = self::getDOMCradle()->createElement('textRun', $elt->wholeText);
                            }

                            $marshaller = $this->getMarshallerFactory()->createMarshaller($elt);
                            $cpt = $marshaller->unmarshall($elt);

                            if ($cpt instanceof FlowStatic) {
                                $content[] = $cpt;
                            } else {
                                $msg = "'testFeedback' elements cannot contain '" . $cpt->getQtiClassName() . "' elements.";
                                throw new UnmarshallingException($msg, $element);
                            }
                        }

                        $object = new TestFeedback($identifier, $outcomeIdentifier, $content);
                        $object->setAccess(($access == 'atEnd') ? TestFeedbackAccess::AT_END : TestFeedbackAccess::DURING);
                        $object->setShowHide(($showHide == 'show') ? ShowHide::SHOW : ShowHide::HIDE);

                        if (($title = $this->getDOMElementAttributeAs($element, 'title', 'string')) !== null) {
                            $object->setTitle($title);
                        }

                        return $object;
                    } else {
                        $msg = "The mandatory 'access' attribute is missing from element '" . $element->localName . "'.";
                        throw new UnmarshallingException($msg, $element);
                    }
                } else {
                    $msg = "The mandatory 'showHide' attribute is missing from element '" . $element->localName . "'.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory 'outcomeIdentifier' attribute is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'testFeedback';
    }

    /**
     * Extract the content (direct children) of a testFeedback element.
     *
     * @param DOMElement $element The testFeedback element you want to extract the content.
     * @return string The content of the feedback element as a string. If there is no extractable content, an empty string is returned.
     * @throws InvalidArgumentException If $element is not a testFeedback element.
     */
    protected static function extractContent(DOMElement $element)
    {
        if ($element->localName == 'testFeedback') {
            return preg_replace('#</{0,1}testFeedback.*?>#iu', '', $element->ownerDocument->saveXML($element));
        } else {
            throw new InvalidArgumentException("The element must be a QTI testFeedbackElement, '" . $element->localName . "' element given.");
        }
    }
}
