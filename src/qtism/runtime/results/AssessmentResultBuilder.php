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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bogaerts Jérôme, <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\AssessmentItemRef;
use qtism\data\results\AssessmentResult;
use qtism\data\results\Context;
use qtism\data\results\ItemResultCollection;
use qtism\data\results\TestResult;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentTestSession;

/**
 * Class AssessmentResultBuilder
 *
 * This class aims at building QTI AssessmentResult objects from a given
 * AssessmentTestSession object.
 */
class AssessmentResultBuilder extends AbstractResultBuilder
{

    /**
     * Build Result
     *
     * @return AssessmentResult
     */
    public function buildResult()
    {
        /** @var AssessmentTestSession $state */
        $state = $this->state;

        $assessmentContext = new Context();
        $assessmentResult = new AssessmentResult($assessmentContext);

        $testResult = new TestResult(
            new QtiIdentifier($state->getSessionId()),
            $this->getLastProcessingTime()
        );

        $testResult->setItemVariables($this->buildVariables());
        $assessmentResult->setTestResult($testResult);

        $itemResults = new ItemResultCollection();

        /** @var AssessmentItemRef $assessmentItemRef */
        foreach ($state->getRoute()->getAssessmentItemRefs() as $assessmentItemRef) {
            $assessmentItemSessions = $state->getAssessmentItemSessions($assessmentItemRef->getIdentifier());

            /** @var AssessmentItemSession $assessmentItemSession */
            foreach ($assessmentItemSessions as $assessmentItemSession) {
                $itemResultBuilder = new ItemResultBuilder($assessmentItemSession);
                $itemResults[] = $itemResultBuilder->buildResult();
            }
        }

        $assessmentResult->setItemResults($itemResults);

        return $assessmentResult;
    }

    /**
     * Get the variables
     *
     * Get the variables representing the intrinsic state of the AssessmentTestSession.
     *
     * @return \qtism\runtime\common\VariableCollection
     */
    protected function getAllVariables()
    {
        return $this->state->getAllVariables();
    }
}
