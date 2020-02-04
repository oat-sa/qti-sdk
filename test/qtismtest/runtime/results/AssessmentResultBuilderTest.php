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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bogaerts Jérôme <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\AssessmentResult;
use qtism\data\results\TestResult;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\results\AssessmentResultBuilder;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

class AssessmentResultBuilderTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testBasic()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/linear_5_items.xml');
        $session->beginTestSession();

        $session->beginAttempt();
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('ChoiceA')
                )
            ])
        );

        $assessmentResultBuilder = new AssessmentResultBuilder($session);
        $assessmentResult = $assessmentResultBuilder->buildResult();

        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $testResult = $assessmentResult->getTestResult();
        $this->assertInstanceOf(TestResult::class, $testResult);
        $this->assertCount(0, $testResult->getItemVariables());

        $itemResults = $assessmentResult->getItemResults();
        $this->assertCount(5, $itemResults);
    }
}
