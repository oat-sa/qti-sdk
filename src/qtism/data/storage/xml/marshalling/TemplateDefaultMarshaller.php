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
use qtism\data\QtiComponent;
use qtism\data\state\TemplateDefault;

/**
 * Marshalling/Unmarshalling implementation for templateDefault.
 */
class TemplateDefaultMarshaller extends Marshaller
{
    /**
     * Marshall a TemplateDefault object into a DOMElement object.
     *
     * @param QtiComponent $component A TemplateDefault object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());

        $expr = $component->getExpression();
        $exprMarshaller = $this->getMarshallerFactory()->createMarshaller($expr);
        $exprElt = $exprMarshaller->marshall($expr);

        $element->appendChild($exprElt);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI templateDefault element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return TemplateDefault A templateDefault object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException If the mandatory attribute 'templateIdentifier' is missing or has an unexpected number of expressions.
     */
    protected function unmarshall(DOMElement $element): TemplateDefault
    {
        if (($tplIdentifier = $this->getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
            $expressionElt = self::getFirstChildElement($element);

            if ($expressionElt !== false) {
                $exprMarshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
                $expr = $exprMarshaller->unmarshall($expressionElt);
            } else {
                $msg = "Element '" . $element->localName . "' does not contain its mandatory expression.";
                throw new UnmarshallingException($msg, $element);
            }

            return new TemplateDefault($tplIdentifier, $expr);
        } else {
            $msg = "The mandatory attribute 'templateIdentifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'templateDefault';
    }
}
