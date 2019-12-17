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

use DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\data\AssessmentItemRef;
use qtism\data\results\AssessmentResult;
use qtism\data\results\Context;
use qtism\data\results\ItemResultCollection;
use qtism\data\results\SessionIdentifier;
use qtism\data\results\SessionIdentifierCollection;
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
    const CUSTOM_PARAMETERS_SESSION_IDENTIFIER = 'LtiCustomParameters';
    const CUSTOM_PARAMETERS_URI = 'http://lti-custom-parameter/';
    
    /**
     * Build Result
     *
     * @param array $customParameters LTI custom parameters to be inserted into the results' context.
     *
     * @return AssessmentResult
     */
    public function buildResult(array $ltiCustomParameters = null)
    {
        /** @var AssessmentTestSession $state */
        $state = $this->state;

        $assessmentContext = $this->buildContext($ltiCustomParameters);
        $assessmentResult = new AssessmentResult($assessmentContext);

        $testResult = new TestResult(
            new QtiIdentifier($state->getSessionId()),
            new DateTime()
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

    /**
     * Creates context from optional LTI custom parameters.
     * @param array $ltiCustomParameters
     *
     * @return Context
     */
    private function buildContext(array $ltiCustomParameters): Context
    {
        // Only "custom_" prefixed keys are taken into account.
        $customParameters = array_filter(
            $ltiCustomParameters,
            static function ($key) {
                return strpos($key, 'custom_') === 0;
            },
            ARRAY_FILTER_USE_KEY
        );
        
        if (count($customParameters) === 0) {
            return new Context();
        }
        
        $sessionIdentifier = new SessionIdentifier(new QtiUri(self::CUSTOM_PARAMETERS_URI), new QtiIdentifier(base64_encode(json_encode($customParameters))));
        $customParametersIdentifier = new QtiIdentifier(self::CUSTOM_PARAMETERS_SESSION_IDENTIFIER);
        $customParametersCollection = new SessionIdentifierCollection([$sessionIdentifier]);
        return new Context($customParametersIdentifier, $customParametersCollection);
    }
}
