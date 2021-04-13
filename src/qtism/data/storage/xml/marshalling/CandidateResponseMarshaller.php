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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\QtiComponent;
use qtism\data\results\CandidateResponse;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;

/**
 * Class CandidateResponseMarshaller
 */
class CandidateResponseMarshaller extends Marshaller
{
    /**
     * @param QtiComponent $component
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        if ($component->hasValues()) {
            /** @var Value $value */
            foreach ($component->getValues() as $value) {
                $valueElement = $this->getMarshallerFactory()
                    ->createMarshaller($value)
                    ->marshall($value);
                $element->appendChild($valueElement);
            }
        }

        return $element;
    }

    /**
     * @param DOMElement $element
     * @return QtiComponent|CandidateResponse
     * @throws MarshallerNotFoundException
     */
    protected function unmarshall(DOMElement $element)
    {
        $valuesElements = $this->getChildElementsByTagName($element, 'value');
        if (!empty($valuesElements)) {
            $values = [];
            foreach ($valuesElements as $valuesElement) {
                $values[] = $this->getMarshallerFactory()
                    ->createMarshaller($valuesElement)
                    ->unmarshall($valuesElement);
            }
            $valueCollection = new ValueCollection($values);
        } else {
            $valueCollection = null;
        }

        return new CandidateResponse($valueCollection);
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'candidateResponse';
    }
}
