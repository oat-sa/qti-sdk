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

namespace qtismtest\runtime\expressions\operators\custom\text;

use qtism\common\enums\BaseType;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiString;
use qtism\data\expressions\operators\CustomOperator;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\BaseValue;
use qtism\runtime\expressions\operators\OperandsCollection;
use qti\customOperators\text\StringToNumber;
use qtismtest\QtiSmTestCase;
 
/**
 * Tests for StringToNumber custom operator.
 */
class StringToNumberTest extends QtiSmTestCase {
	
    public function testSimpleOne() {
        $baseValue = new BaseValue(BaseType::STRING, '13,37');
        $customOperator = new CustomOperator(
            new ExpressionCollection(array($baseValue)),
            '<customOperator class="qti.customOperators.text.StringToNumber">
                <baseValue baseType="string">13,37</baseValue>
            </customOperator>'
        );
        $operands = new OperandsCollection(array(new QtiString('13,37')));
        $operator = new StringToNumber($customOperator, $operands);
        $result = $operator->process();
        
        $this::assertEquals($result->getValue(), (float)1337);
    }
    
    public function testSimpleTwo() {
        $baseValue = new BaseValue(BaseType::STRING, '13.37');
        $customOperator = new CustomOperator(
            new ExpressionCollection(array($baseValue)),
            '<customOperator class="qti.customOperators.text.StringToNumber">
                <baseValue baseType="string">13.37</baseValue>
            </customOperator>'
        );
        $operands = new OperandsCollection(array(new QtiString('13.37')));
        $operator = new StringToNumber($customOperator, $operands);
        $result = $operator->process();

        $this::assertEquals(round($result->getValue(), 2), round(13.37, 2));
    }
    
    public function testReturnsNull() {
        $baseValue = new BaseValue(BaseType::BOOLEAN, false);
        $customOperator = new CustomOperator(
            new ExpressionCollection(array($baseValue)),
            '<customOperator class="qti.customOperators.text.StringToNumber">
                <baseValue baseType="boolean">false</baseValue>
            </customOperator>'
        );
        $operands = new OperandsCollection(array(new QtiBoolean(false)));
        $operator = new StringToNumber($customOperator, $operands);
        $result = $operator->process();

        $this::assertNull($result);
    }
}
