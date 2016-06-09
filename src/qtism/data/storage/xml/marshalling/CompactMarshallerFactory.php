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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

/**
 * A specialized marshaller factory focusing on components involved in CompactXml documents.
 * 
 * In addition with the QTI 2.1 related marshallers, the following marshallers are mapped to this factory:
 * 
 * * ExtendedAssessmentItemRefMarshaller
 * * ExtendedAssessmentSectionMarshaller
 * * ExtendedTestPartMarshaller
 * * ExtendedAssessmentTestMarshaller
 * * RubricBlockRefMarshaller
 * * TestFeedbackRefMarshaller
 * * ModalFeedbackRuleMarshaller
 * * ShufflingMarshaller
 * * ShufflingGroupMarshaller
 * * ResponseValidityConstraintMarshaller
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class CompactMarshallerFactory extends Qti21MarshallerFactory
{
    /**
     * Create a new CompactMarshallerFactory object.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addMappingEntry('assessmentItemRef', 'qtism\\data\\storage\\xml\\marshalling\\ExtendedAssessmentItemRefMarshaller');
        $this->addMappingEntry('assessmentSection', 'qtism\\data\\storage\\xml\\marshalling\\ExtendedAssessmentSectionMarshaller');
        $this->addMappingEntry('testPart', 'qtism\\data\\storage\\xml\\marshalling\\ExtendedTestPartMarshaller');
        $this->addMappingEntry('assessmentTest', 'qtism\\data\\storage\\xml\\marshalling\\ExtendedAssessmentTestMarshaller');
        $this->addMappingEntry('rubricBlockRef', 'qtism\\data\\storage\\xml\\marshalling\\RubricBlockRefMarshaller');
        $this->addMappingEntry('testFeedbackRef', 'qtism\\data\\storage\\xml\\marshalling\\TestFeedbackRefMarshaller');
        $this->addMappingEntry('modalFeedbackRule', 'qtism\\data\\storage\\xml\\marshalling\\ModalFeedbackRuleMarshaller');
        $this->addMappingEntry('shuffling', 'qtism\\data\\storage\\xml\\marshalling\\ShufflingMarshaller');
        $this->addMappingEntry('shufflingGroup', 'qtism\\data\\storage\\xml\\marshalling\\ShufflingGroupMarshaller');
        $this->addMappingEntry('responseValidityConstraint', 'qtism\\data\\storage\\xml\\marshalling\\ResponseValidityConstraintMarshaller');
    }
}
