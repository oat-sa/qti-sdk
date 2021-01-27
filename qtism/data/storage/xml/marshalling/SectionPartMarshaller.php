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
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;
use qtism\data\SectionPart;

/**
 * Marshalling/Unmarshalling implementation for sectionPart.
 */
class SectionPartMarshaller extends Marshaller
{
    /**
     * Marshall a SectionPart object into a DOMElement object.
     *
     * @param QtiComponent $component A SectionPart object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'required', $component->isRequired());
        $this->setDOMElementAttribute($element, 'fixed', $component->isFixed());

        foreach ($component->getPreConditions() as $preCondition) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($preCondition);
            $element->appendChild($marshaller->marshall($preCondition));
        }

        foreach ($component->getBranchRules() as $branchRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($branchRule);
            $element->appendChild($marshaller->marshall($branchRule));
        }

        if ($component->getItemSessionControl() != null) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getItemSessionControl());
            $element->appendChild($marshaller->marshall($component->getItemSessionControl()));
        }

        if ($component->getTimeLimits() != null) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getTimeLimits());
            $element->appendChild($marshaller->marshall($component->getTimeLimits()));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sectionPart element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A SectionPart object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $object = new SectionPart($identifier);

            if (($required = $this->getDOMElementAttributeAs($element, 'required', 'boolean')) !== null) {
                $object->setRequired($required);
            }

            if (($fixed = $this->getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                $object->setFixed($fixed);
            }

            $preConditionElts = $this->getChildElementsByTagName($element, 'preCondition');
            if (count($preConditionElts) > 0) {
                $preConditions = new PreConditionCollection();
                for ($i = 0; $i < count($preConditionElts); $i++) {
                    $marshaller = $this->getMarshallerFactory()->createMarshaller($preConditionElts[$i]);
                    $preConditions[] = $marshaller->unmarshall($preConditionElts[$i]);
                }
                $object->setPreConditions($preConditions);
            }

            $branchRuleElts = $this->getChildElementsByTagName($element, 'branchRule');
            if (count($branchRuleElts) > 0) {
                $branchRules = new BranchRuleCollection();
                for ($i = 0; $i < count($branchRuleElts); $i++) {
                    $marshaller = $this->getMarshallerFactory()->createMarshaller($branchRuleElts[$i]);
                    $branchRules[] = $marshaller->unmarshall($branchRuleElts[$i]);
                }
                $object->setBranchRules($branchRules);
            }

            $itemSessionControlElts = $this->getChildElementsByTagName($element, 'itemSessionControl');
            if (count($itemSessionControlElts) == 1) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($itemSessionControlElts[0]);
                $object->setItemSessionControl($marshaller->unmarshall($itemSessionControlElts[0]));
            }

            $timeLimitsElts = $this->getChildElementsByTagName($element, 'timeLimits');
            if (count($timeLimitsElts) == 1) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($timeLimitsElts[0]);
                $object->setTimeLimits($marshaller->unmarshall($timeLimitsElts[0]));
            }

            return $object;
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'sectionPart';
    }
}
