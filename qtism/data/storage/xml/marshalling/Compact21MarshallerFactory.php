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

/**
 * A specialized marshaller factory focusing on components involved in CompactXml documents.
 *
 * In addition with the QTI 2.1 related marshallers, the following marshallers are mapped to this factory:
 *
 * * ExtendedAssessmentItemRefMarshaller
 * * ExtendedAssessmentSectionMarshaller
 */
class Compact21MarshallerFactory extends Qti21MarshallerFactory
{
    /**
     * Create a new CompactMarshallerFactory object.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addMappingEntry('assessmentItemRef', ExtendedAssessmentItemRefMarshaller::class);
        $this->addMappingEntry('assessmentSection', ExtendedAssessmentSectionMarshaller::class);
    }
}
