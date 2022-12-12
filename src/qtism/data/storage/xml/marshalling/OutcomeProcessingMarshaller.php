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
use qtism\data\processing\OutcomeProcessing;
use qtism\data\QtiComponent;
use qtism\data\rules\OutcomeRuleCollection;

/**
 * Marshalling/Unmarshalling implementation for outcomeProcessing.
 */
class OutcomeProcessingMarshaller extends Marshaller
{
    /**
     * Marshall an OutcomeProcessing object into a DOMElement object.
     *
     * @param QtiComponent $component An OutcomeProcessing object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        foreach ($component->getOutcomeRules() as $outcomeRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeRule);
            $element->appendChild($marshaller->marshall($outcomeRule));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI outcomeProcessing element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return OutcomeProcessing An OutcomeProcessing object.
     * @throws MarshallerNotFoundException
     */
    protected function unmarshall(DOMElement $element): OutcomeProcessing
    {
        $outcomeRules = new OutcomeRuleCollection();

        foreach (self::getChildElements($element) as $outcomeRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeRule);
            $outcomeRules[] = $marshaller->unmarshall($outcomeRule);
        }

        return new OutcomeProcessing($outcomeRules);
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'outcomeProcessing';
    }
}
