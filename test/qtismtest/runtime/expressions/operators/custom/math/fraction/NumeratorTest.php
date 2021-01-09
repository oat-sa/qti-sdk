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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\runtime\expressions\operators\custom\math\fraction;

use qti\customOperators\math\fraction\Numerator;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\CustomOperator;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;

/**
 * Tests for Numerator custom operator.
 */
class NumeratorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $baseValue = new BaseValue(BaseType::STRING, '1/2');
        $customOperator = new CustomOperator(
            new ExpressionCollection([$baseValue]),
            '<customOperator class="qti.customOperators.math.Numerator"><baseValue baseType="string">1/2</baseValue></customOperator>'
        );
        $operands = new OperandsCollection([new QtiString('1/2')]);
        $operator = new Numerator($customOperator, $operands);
        $result = $operator->process();

        $this::assertEquals(1, $result->getValue());
    }

    public function testReturnsNullOne()
    {
        $baseValue = new BaseValue(BaseType::BOOLEAN, false);
        $customOperator = new CustomOperator(
            new ExpressionCollection([$baseValue]),
            '<customOperator class="qti.customOperators.math.Numerator"><baseValue baseType="boolean">false</baseValue></customOperator>'
        );
        $operands = new OperandsCollection([new QtiBoolean(false)]);
        $operator = new Numerator($customOperator, $operands);
        $result = $operator->process();

        $this::assertSame(null, $result);
    }

    public function testReturnsNullTwo()
    {
        $baseValue = new BaseValue(BaseType::BOOLEAN, false);
        $customOperator = new CustomOperator(
            new ExpressionCollection([$baseValue]),
            '<customOperator class="qti.customOperators.math.Numerator"><baseValue baseType="string">bla</baseValue></customOperator>'
        );
        $operands = new OperandsCollection([new QtiBoolean(false)]);
        $operator = new Numerator($customOperator, $operands);
        $result = $operator->process();

        $this::assertNull($result);
    }
}
