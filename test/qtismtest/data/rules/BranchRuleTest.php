<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

namespace qtismtest\data\rules;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\BranchRule;
use qtismtest\QtiSmTestCase;

/**
 * Class BranchRuleTest
 */
class BranchRuleTest extends QtiSmTestCase
{
    public function testCreateBranchRule(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $target = 'TARGET_IDENTIFIER';
        $branchRule = new BranchRule($expression, $target);

        $this->assertSame($expression, $branchRule->getExpression());
        $this->assertEquals($target, $branchRule->getTarget());
        $this->assertNull($branchRule->getParentIdentifier());
    }

    public function testSetGetExpression(): void
    {
        $expression1 = new BaseValue(BaseType::BOOLEAN, true);
        $expression2 = new BaseValue(BaseType::INTEGER, 42);
        $branchRule = new BranchRule($expression1, 'TARGET');

        $this->assertSame($expression1, $branchRule->getExpression());

        $branchRule->setExpression($expression2);
        $this->assertSame($expression2, $branchRule->getExpression());
    }

    public function testSetGetTarget(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $branchRule = new BranchRule($expression, 'TARGET1');

        $this->assertEquals('TARGET1', $branchRule->getTarget());

        $branchRule->setTarget('TARGET2');
        $this->assertEquals('TARGET2', $branchRule->getTarget());
    }

    public function testSetTargetWithInvalidIdentifier(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $branchRule = new BranchRule($expression, 'VALID_TARGET');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'Target' must be a valid QTI Identifier.");
        $branchRule->setTarget('invalid target with spaces');
    }

    public function testCreateBranchRuleWithInvalidTarget(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'Target' must be a valid QTI Identifier.");
        new BranchRule($expression, 'invalid identifier');
    }

    public function testSetGetParentIdentifier(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $branchRule = new BranchRule($expression, 'TARGET');

        $this->assertNull($branchRule->getParentIdentifier());

        $branchRule->setParentIdentifier('PARENT_ID');
        $this->assertEquals('PARENT_ID', $branchRule->getParentIdentifier());
    }

    public function testSetParentIdentifierWithInvalidIdentifier(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $branchRule = new BranchRule($expression, 'TARGET');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Parent" must be a valid QTI Identifier.');
        $branchRule->setParentIdentifier('invalid parent identifier');
    }

    public function testGetQtiClassName(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $branchRule = new BranchRule($expression, 'TARGET');

        $this->assertEquals('branchRule', $branchRule->getQtiClassName());
    }

    public function testGetComponents(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);
        $branchRule = new BranchRule($expression, 'TARGET');

        $components = $branchRule->getComponents();
        $this->assertInstanceOf(QtiComponentCollection::class, $components);
        $this->assertCount(1, $components);
        $this->assertSame($expression, $components[0]);
    }

    public function testSpecialTargetConstants(): void
    {
        $expression = new BaseValue(BaseType::BOOLEAN, true);

        $exitTest = new BranchRule($expression, BranchRule::EXIT_TEST);
        $this->assertEquals(BranchRule::EXIT_TEST, $exitTest->getTarget());

        $exitTestPart = new BranchRule($expression, BranchRule::EXIT_TESTPART);
        $this->assertEquals(BranchRule::EXIT_TESTPART, $exitTestPart->getTarget());

        $exitSection = new BranchRule($expression, BranchRule::EXIT_SECTION);
        $this->assertEquals(BranchRule::EXIT_SECTION, $exitSection->getTarget());
    }
}

