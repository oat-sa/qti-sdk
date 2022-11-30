<?php

declare(strict_types=1);

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
use qtism\data\processing\ResponseProcessing;
use qtism\data\QtiComponent;
use qtism\data\rules\ResponseRuleCollection;

/**
 * Marshalling/Unmarshalling implementation for responseProcessing.
 */
class ResponseProcessingMarshaller extends Marshaller
{
    /**
     * Marshall a ResponseProcessing object into a DOMElement object.
     *
     * @param QtiComponent $component A ResponseProcessing object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        if ($component->hasTemplate() === true) {
            $this->setDOMElementAttribute($element, 'template', $component->getTemplate());
        }

        if ($component->hasTemplateLocation() === true) {
            $this->setDOMElementAttribute($element, 'templateLocation', $component->getTemplateLocation());
        }

        foreach ($component->getResponseRules() as $responseRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseRule);
            $element->appendChild($marshaller->marshall($responseRule));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI responseProcessing element.
     *
     * @param DOMElement $element A DOMElement object.
     * @param ResponseProcessing|null $responseProcessing
     * @return QtiComponent A ResponseProcessing object.
     * @throws MarshallerNotFoundException
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(
        DOMElement $element,
        ResponseProcessing $responseProcessing = null
    ): ResponseProcessing {
        $responseRuleElts = self::getChildElements($element);

        $responseRules = new ResponseRuleCollection();
        for ($i = 0; $i < count($responseRuleElts); $i++) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseRuleElts[$i]);
            $responseRules[] = $marshaller->unmarshall($responseRuleElts[$i]);
        }

        if ($responseProcessing === null) {
            $object = new ResponseProcessing($responseRules);
        } else {
            $object = $responseProcessing;
            $object->setResponseRules($responseRules);
        }

        if (($template = $this->getDOMElementAttributeAs($element, 'template')) !== null) {
            $object->setTemplate($template);
        }

        if (($templateLocation = $this->getDOMElementAttributeAs($element, 'templateLocation')) !== null) {
            $object->setTemplateLocation($templateLocation);
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'responseProcessing';
    }
}
