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

class CandidateResponseMarshaller extends Marshaller
{
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());

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

    protected function unmarshall(DOMElement $element)
    {
        $valuesElements = self::getChildElementsByTagName($element, 'value');
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

    public function getExpectedQtiClassName()
    {
        return 'candidateResponse';
    }
}
