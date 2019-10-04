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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille, <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use oat\dtms\DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\data\QtiComponent;
use qtism\data\results\ItemResult;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\SessionStatus;

/**
 * Class ItemResultMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 *
 * @package qtism\data\storage\xml\marshalling
 */
class ItemResultMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|ItemResult $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());
        $element->setAttribute('identifier', $component->getIdentifier());
        $element->setAttribute('datestamp', $component->getDatestamp()->format(DateTime::ISO8601));
        $element->setAttribute('sessionStatus', SessionStatus::getNameByConstant($component->getSessionStatus()));

        if ($component->hasSequenceIndex()) {
            $element->setAttribute('sequenceIndex', $component->getSequenceIndex());
        }

        if ($component->hasCandidateComment()) {
            $candidateCommentElement = self::getDOMCradle()->createElement('candidateComment');
            $candidateCommentElement->textContent = $component->getCandidateComment();
            $element->appendChild($candidateCommentElement);
        }

        if ($component->hasItemVariables()) {
            foreach ($component->getItemVariables() as $variable) {
                $element->appendChild($this->getMarshallerFactory()->createMarshaller($variable)->marshall($variable));
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sessionIdentifier element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A QtiComponent object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (!$element->hasAttribute('identifier')) {
            throw new UnmarshallingException('ItemResult element must have identifier attribute', $element);
        }

        if (!$element->hasAttribute('datestamp')) {
            throw new UnmarshallingException('ItemResult element must have datestamp attribute', $element);
        }

        if (!$element->hasAttribute('sessionStatus')) {
            throw new UnmarshallingException('ItemResult element must have sessionStatus attribute', $element);
        }

        $identifier = new QtiIdentifier($element->getAttribute('identifier'));
        $datestamp = new DateTime($element->getAttribute('datestamp'));
        $sessionStatus = SessionStatus::getConstantByName($element->getAttribute('sessionStatus'));

        $variableElements = array_merge(
            self::getChildElementsByTagName($element, 'responseVariable'),
            self::getChildElementsByTagName($element, 'outcomeVariable'),
            self::getChildElementsByTagName($element, 'templateVariable')
        );

        if (!empty($variableElements)) {
            $variables = [];
            foreach ($variableElements as $variableElement) {
                $variables[] = $this->getMarshallerFactory()
                    ->createMarshaller($variableElement)
                    ->unmarshall($variableElement);
            }
            $variableCollection = new ItemVariableCollection($variables);
        } else {
            $variableCollection = null;
        }

        $candidateCommentElements = self::getChildElementsByTagName($element, 'candidateComment');
        if (!empty($candidateCommentElements)) {
            $candidateCommentElement = array_shift($candidateCommentElements);
            $candidateComment = new QtiString($candidateCommentElement->textContent);
        } else {
            $candidateComment = null;
        }

        $sequenceIndex = $element->hasAttribute('sequenceIndex')
            ? new QtiInteger((int) $element->getAttribute('sequenceIndex'))
            : null;

        return new ItemResult($identifier, $datestamp, $sessionStatus, $variableCollection, $candidateComment, $sequenceIndex);
    }

    /**
     * Get the class name/tag name of the QtiComponent/DOMElement which can be handled
     * by the Marshaller's implementation.
     *
     * Return an empty string if the marshaller implementation does not expect a particular
     * QTI class name.
     *
     * @return string A QTI class name or an empty string.
     */
    public function getExpectedQtiClassName()
    {
        return 'itemResult';
    }

}