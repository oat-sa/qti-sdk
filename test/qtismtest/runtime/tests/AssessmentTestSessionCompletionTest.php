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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

class AssessmentTestSessionCompletionTest extends QtiSmAssessmentTestSessionTestCase
{
    /**
     * In linear mode, items are considered completed if they were
     * presented at least one time.
     *
     * Please note that if $identifiers contain
     *
     * * 'skip' strings, the item subject to the test will skip the current item instead of ending the attempt.
     * * 'moveNext' strings, the item subject to the test will not be end-attempted and a moveNext will be performed instead.
     *
     * @dataProvider completionPureLinearProvider
     * @param string $testFile The Compact test definition to be run as a candidate session.
     * @param array $identifiers An array of response identifier to be given for each item.
     * @param integer $finalNumberCompleted The expected number of completed items when the session closes.
     */
    public function testCompletion($testFile, $identifiers, $finalNumberCompleted)
    {
        $session = self::instantiate($testFile);
        $session->beginTestSession();

        // Nothing completed at this time.
        $this->assertSame(0, $session->numberCompleted());

        $i = 1;
        $movedNext = 0;
        foreach ($identifiers as $identifier) {
            $this->assertSame($i - 1 - $movedNext, $session->numberCompleted());

            $session->beginAttempt();

            if ($identifier === 'skip') {
                $session->endAttempt(new State());
            } elseif ($identifier === 'moveNext') {
                $session->moveNext();
                $movedNext++;
            } else {
                $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier($identifier))]));
            }

            $this->assertSame($i - $movedNext, $session->numberCompleted());

            if ($identifier !== 'moveNext') {
                $session->moveNext();
            }

            $i++;
        }

        // Final completion check.
        $this->assertSame($finalNumberCompleted, $session->numberCompleted());

        // We must reach the end of the test session.
        $this->assertFalse($session->isRunning());
    }

    public function completionPureLinearProvider()
    {
        return [
            [self::samplesDir() . '/custom/runtime/linear_5_items.xml', ['skip', 'skip', 'skip', 'skip', 'skip'], 5],
            [self::samplesDir() . '/custom/runtime/linear_5_items.xml', ['ChoiceA', 'skip', 'ChoiceC', 'ChoiceD', 'ChoiceE'], 5],
            [self::samplesDir() . '/custom/runtime/linear_5_items.xml', ['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD', 'ChoiceE'], 5],
            [self::samplesDir() . '/custom/runtime/completion/linear_10_items_2_testparts.xml', ['skip', 'skip', 'skip', 'skip', 'skip', 'skip', 'skip', 'skip', 'skip', 'skip'], 10],
            [self::samplesDir() . '/custom/runtime/completion/linear_10_items_2_testparts.xml', ['ChoiceA', 'skip', 'ChoiceC', 'skip', 'ChoiceE', 'skip', 'ChoiceG', 'skip', 'ChoiceI', 'skip'], 10],
            [self::samplesDir() . '/custom/runtime/completion/linear_10_items_2_testparts.xml', ['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD', 'ChoiceE', 'ChoiceF', 'ChoiceG', 'ChoiceH', 'ChoiceI', 'ChoiceJ'], 10],
            [self::samplesDir() . '/custom/runtime/nonlinear_5_items.xml', ['moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext'], 0],
            [self::samplesDir() . '/custom/runtime/nonlinear_5_items.xml', ['ChoiceA', 'moveNext', 'choiceC', 'ChoiceD', 'ChoiceE'], 4],
            [self::samplesDir() . '/custom/runtime/nonlinear_5_items.xml', ['ChoiceA', 'ChoiceB', 'choiceC', 'ChoiceD', 'ChoiceE'], 5],
            [self::samplesDir() . '/custom/runtime/completion/nonlinear_10_items_2_testparts.xml', ['moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext'], 0],
            [self::samplesDir() . '/custom/runtime/completion/nonlinear_10_items_2_testparts.xml', ['ChoiceA', 'moveNext', 'ChoiceC', 'moveNext', 'ChoiceE', 'moveNext', 'ChoiceG', 'moveNext', 'ChoiceI', 'moveNext'], 5],
            [self::samplesDir() . '/custom/runtime/completion/nonlinear_10_items_2_testparts.xml', ['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD', 'ChoiceE', 'ChoiceF', 'ChoiceG', 'ChoiceH', 'ChoiceI', 'ChoiceJ'], 10],
            [self::samplesDir() . '/custom/runtime/completion/linearnonlinear_10_items_2_testparts.xml', ['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD', 'ChoiceE', 'ChoiceF', 'ChoiceG', 'ChoiceH', 'ChoiceI', 'ChoiceJ'], 10],
            [self::samplesDir() . '/custom/runtime/completion/linearnonlinear_10_items_2_testparts.xml', ['skip', 'skip', 'skip', 'skip', 'skip', 'moveNext', 'moveNext', 'moveNext', 'moveNext', 'moveNext'], 5],
            [self::samplesDir() . '/custom/runtime/completion/linearnonlinear_10_items_2_testparts.xml', ['ChoiceA', 'skip', 'ChoiceC', 'skip', 'ChoiceE', 'moveNext', 'ChoiceG', 'moveNext', 'ChoiceI', 'moveNext'], 7],
        ];
    }
}
