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

namespace qtismtest\runtime\expressions\operators\custom\math\graph;

use qti\customOperators\math\graph\CountPointsThatSatisfyEquation;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\NullValue;
use qtism\data\expressions\operators\CustomOperator;
use qtism\data\expressions\operators\Multiple;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;

/**
 * Tests for CountPointsThatSatisfyEquation custom operator.
 */
class CountPointsThatSatisfyEquationTest extends QtiSmTestCase
{
    public function testSimpleOne()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::POINT, new QtiPoint(0, 0)),
                    new BaseValue(BaseType::POINT, new QtiPoint(1, 1)),
                    new BaseValue(BaseType::POINT, new QtiPoint(2, 4)),
                    new BaseValue(BaseType::POINT, new QtiPoint(3, 9)),
                    new BaseValue(BaseType::POINT, new QtiPoint(4, 16)),
                    new BaseValue(BaseType::POINT, new QtiPoint(5, 25)),
                    new BaseValue(BaseType::POINT, new QtiPoint(6, 36)),
                    new BaseValue(BaseType::POINT, new QtiPoint(7, 49)),
                ]
            )
        );
        $equation = new BaseValue(BaseType::STRING, 'x ^ 2');

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="point">0 0</baseValue><baseValue baseType="point">1 1</baseValue><baseValue baseType="point">2 4</baseValue><baseValue baseType="point">3 9</baseValue><baseValue baseType="point">4 16</baseValue><baseValue baseType="point">5 25</baseValue><baseValue baseType="point">6 36</baseValue><baseValue baseType="point">7 49</baseValue></multiple><baseValue baseType="string">y = x ^ 2</baseValue></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::POINT,
                    [
                        new QtiPoint(0, 0),
                        new QtiPoint(1, 1),
                        new QtiPoint(2, 4),
                        new QtiPoint(3, 9),
                        new QtiPoint(4, 16),
                        new QtiPoint(5, 25),
                        new QtiPoint(6, 36),
                        new QtiPoint(7, 49),
                    ]
                ),
                new QtiString('y = x ^ 2'),
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertEquals(8, $result->getValue());
    }

    public function testSimpleOneWithStrings()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::STRING, '0 0'),
                    new BaseValue(BaseType::STRING, '1 1'),
                    new BaseValue(BaseType::STRING, '2 4'),
                    new BaseValue(BaseType::STRING, '3 9'),
                    new BaseValue(BaseType::STRING, '4 16'),
                    new BaseValue(BaseType::STRING, '5 25'),
                    new BaseValue(BaseType::STRING, '6 36'),
                    new BaseValue(BaseType::STRING, '7 49'),
                ]
            )
        );
        $equation = new BaseValue(BaseType::STRING, 'x ^ 2');

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="string">0 0</baseValue><baseValue baseType="string">1 1</baseValue><baseValue baseType="string">2 4</baseValue><baseValue baseType="string">3 9</baseValue><baseValue baseType="string">4 16</baseValue><baseValue baseType="string">5 25</baseValue><baseValue baseType="string">6 36</baseValue><baseValue baseType="string">7 49</baseValue></multiple><baseValue baseType="string">y = x ^ 2</baseValue></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::STRING,
                    [
                        new QtiString('0 0'),
                        new QtiString('1 1'),
                        new QtiString('2 4'),
                        new QtiString('3 9'),
                        new QtiString('4 16'),
                        new QtiString('5 25'),
                        new QtiString('6 36'),
                        new QtiString('7 49'),
                    ]
                ),
                new QtiString('y = x ^ 2'),
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertEquals(8, $result->getValue());
    }

    public function testSimpleTwo()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::POINT, new QtiPoint(0, 0)),
                    new BaseValue(BaseType::POINT, new QtiPoint(-1, 1)),
                    new BaseValue(BaseType::POINT, new QtiPoint(2, 4)),
                    new BaseValue(BaseType::POINT, new QtiPoint(3, 9)),
                    new BaseValue(BaseType::POINT, new QtiPoint(4, 16)),
                    new BaseValue(BaseType::POINT, new QtiPoint(5, 25)),
                    new BaseValue(BaseType::POINT, new QtiPoint(14, 35)),
                    new BaseValue(BaseType::POINT, new QtiPoint(-5, 49)),
                ]
            )
        );
        $equation = new BaseValue(BaseType::STRING, 'x ^ 2');

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="point">0 0</baseValue><baseValue baseType="point">-1 1</baseValue><baseValue baseType="point">2 4</baseValue><baseValue baseType="point">3 9</baseValue><baseValue baseType="point">4 16</baseValue><baseValue baseType="point">5 25</baseValue><baseValue baseType="point">14 35</baseValue><baseValue baseType="point">-5 49</baseValue></multiple><baseValue baseType="string">y = x ^ 2</baseValue></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::POINT,
                    [
                        new QtiPoint(0, 0),
                        new QtiPoint(-1, 1),
                        new QtiPoint(2, 4),
                        new QtiPoint(3, 9),
                        new QtiPoint(4, 16),
                        new QtiPoint(5, 25),
                        new QtiPoint(14, 35),
                        new QtiPoint(-5, 49),
                    ]
                ),
                new QtiString('y = x ^ 2'),
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertEquals(6, $result->getValue());
    }

    public function testInvalidEquation()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::POINT, new QtiPoint(0, 0)),
                    new BaseValue(BaseType::POINT, new QtiPoint(-1, 1)),
                    new BaseValue(BaseType::POINT, new QtiPoint(2, 4)),
                    new BaseValue(BaseType::POINT, new QtiPoint(3, 9)),
                    new BaseValue(BaseType::POINT, new QtiPoint(4, 16)),
                    new BaseValue(BaseType::POINT, new QtiPoint(5, 25)),
                    new BaseValue(BaseType::POINT, new QtiPoint(14, 35)),
                    new BaseValue(BaseType::POINT, new QtiPoint(-5, 49)),
                ]
            )
        );
        $equation = new BaseValue(BaseType::STRING, 'x ^ 2');

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="point">0 0</baseValue><baseValue baseType="point">-1 1</baseValue><baseValue baseType="point">2 4</baseValue><baseValue baseType="point">3 9</baseValue><baseValue baseType="point">4 16</baseValue><baseValue baseType="point">5 25</baseValue><baseValue baseType="point">14 35</baseValue><baseValue baseType="point">-5 49</baseValue></multiple><baseValue baseType="string">y = x ^^^^^^ 4 \ vli 2</baseValue></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::POINT,
                    [
                        new QtiPoint(0, 0),
                        new QtiPoint(-1, 1),
                        new QtiPoint(2, 4),
                        new QtiPoint(3, 9),
                        new QtiPoint(4, 16),
                        new QtiPoint(5, 25),
                        new QtiPoint(14, 35),
                        new QtiPoint(-5, 49),
                    ]
                ),
                new QtiString('y = x ^^^^^^ 4 \ vli 2'),
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertNull($result);
    }

    public function testWrongEquationType()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::POINT, new QtiPoint(0, 0)),
                ]
            )
        );
        $equation = new BaseValue(BaseType::INTEGER, 3);

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="point">0 0</baseValue></multiple><baseValue baseType="integer">3</baseValue></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::POINT,
                    [
                        new QtiPoint(0, 0),
                    ]
                ),
                new QtiInteger(3),
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertNull($result);
    }

    public function testNullEquation()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::POINT, new QtiPoint(0, 0)),
                ]
            )
        );
        $equation = new NullValue();

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="point">0 0</baseValue></multiple></null></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::POINT,
                    [
                        new QtiPoint(0, 0),
                    ]
                ),
                null,
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertNull($result);
    }

    public function testWrongPointsType()
    {
        // --- Build Custom Operator PHP Expression Model.
        $points = new Multiple(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::IDENTIFIER, '0 0'),
                ]
            )
        );
        $equation = new BaseValue(BaseType::STRING, 'x = y');

        $customOperator = new CustomOperator(
            new ExpressionCollection(
                [
                    $points,
                    $equation,
                ]
            ),
            '<customOperator class="qti.customOperators.math.graph.CountPointsThatSatisfyEquation"><multiple><baseValue baseType="point">0 0</baseValue></multiple><baseValue baseType="string">x = y</baseValue></customOperator>'
        );

        // --- Build Runtime Operands for PHP Runtime Model.
        $operands = new OperandsCollection(
            [
                new MultipleContainer(
                    BaseType::IDENTIFIER,
                    [
                        new QtiIdentifier('0 0'),
                    ]
                ),
                new QtiString('x = y'),
            ]
        );
        $operator = new CountPointsThatSatisfyEquation($customOperator, $operands);
        $result = $operator->process();

        self::assertNull($result);
    }
}
