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

use DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\ItemResult;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\SessionStatus;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\results\ItemResultBuilder;
use qtismtest\QtiSmAssessmentItemTestCase;

/**
 * Class ItemResultBuilderTest
 */
class ItemResultBuilderTest extends QtiSmAssessmentItemTestCase
{
    public function testBasic()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('ChoiceB')
                ),
            ])
        );

        $itemResultBuilder = new ItemResultBuilder($itemSession);
        $itemResult = $itemResultBuilder->buildResult();

        $this::assertInstanceOf(ItemResult::class, $itemResult);
        $this::assertEquals('Q01', $itemResult->getIdentifier());
        $this::assertInstanceOf(DateTime::class, $itemResult->getDatestamp());
        $this::assertEquals(SessionStatus::STATUS_FINAL, $itemResult->getSessionStatus());

        $variables = $itemResult->getItemVariables();
        $this::assertCount(5, $variables);

        $this::assertInstanceOf(ResultResponseVariable::class, $variables[0]);
        $this::assertEquals('numAttempts', $variables[0]->getIdentifier());

        $this::assertInstanceOf(ResultResponseVariable::class, $variables[1]);
        $this::assertEquals('duration', $variables[1]->getIdentifier());

        $this::assertInstanceOf(ResultOutcomeVariable::class, $variables[2]);
        $this::assertEquals('completionStatus', $variables[2]->getIdentifier());

        $this::assertInstanceOf(ResultOutcomeVariable::class, $variables[3]);
        $this::assertEquals('SCORE', $variables[3]->getIdentifier());

        $this::assertInstanceOf(ResultResponseVariable::class, $variables[4]);
        $this::assertEquals('RESPONSE', $variables[4]->getIdentifier());
    }
}
