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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\state;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\ExternalScored;
use qtism\data\state\MatchTable;
use qtism\data\state\MatchTableEntry;
use qtism\data\state\MatchTableEntryCollection;
use qtism\data\state\OutcomeDeclaration;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeDeclarationTest
 */
class OutcomeDeclarationTest extends QtiSmTestCase
{
    /** @var OutcomeDeclaration */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
    }

    public function testSetInterpretationWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Interpretation must be a string, 'integer' given.");

        $this->subject->setInterpretation(999);
    }

    public function testSetLongInterpretationWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("LongInterpretation must be a string, 'integer' given.");

        $this->subject->setLongInterpretation(999);
    }

    public function testSetNormalMinimumWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("NormalMinimum must be a number or (boolean) false, 'string' given.");

        $this->subject->setNormalMinimum('string');
    }

    public function testSetNormalMaximumWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("NormalMaximum must be a number or (boolean) false, 'string' given.");

        $this->subject->setNormalMaximum('string');
    }

    public function testSetMasteryValueWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("MasteryValue must be a number or (boolean) false, 'string' given.");

        $this->subject->setMasteryValue('string');
    }

    public function getComponentsWithLookupTable()
    {
        $this->subject->setLookupTable(
            new MatchTable(
                new MatchTableEntryCollection(
                    new MatchTableEntry(3, 3.33)
                )
            )
        );

        $components = $this->getComponents();
        $last = $components[count($components) - 1];
        $this->assertInstanceOf(MatchTable::class, $last);
    }

    public function testExternalScoredAccessors()
    {
        $this->assertFalse($this->subject->isExternallyScored());
        $this->assertFalse($this->subject->isScoredByHuman());
        $this->assertFalse($this->subject->isScoredByExternalMachine());

        $this->subject->setExternalScored(ExternalScored::getConstantByName('human'));

        $this->assertTrue($this->subject->isExternallyScored());
        $this->assertTrue($this->subject->isScoredByHuman());
        $this->assertFalse($this->subject->isScoredByExternalMachine());

        $this->subject->setExternalScored(ExternalScored::getConstantByName('externalMachine'));

        $this->assertTrue($this->subject->isExternallyScored());
        $this->assertFalse($this->subject->isScoredByHuman());
        $this->assertTrue($this->subject->isScoredByExternalMachine());

        $this->subject->setExternalScored();

        $this->assertFalse($this->subject->isExternallyScored());
        $this->assertFalse($this->subject->isScoredByHuman());
        $this->assertFalse($this->subject->isScoredByExternalMachine());
    }
}
