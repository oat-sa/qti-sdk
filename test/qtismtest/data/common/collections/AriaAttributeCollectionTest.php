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

namespace qtismtest\data\common\collections;

use InvalidArgumentException;
use qtism\data\common\collections\AriaAttributeCollection;
use qtism\data\common\enums\Aria;
use qtismtest\QtiSmTestCase;

class AriaAttributeCollectionTest extends QtiSmTestCase
{
    /**
     * @param $input
     * @dataProvider constructProvider
     */
    public function testConstruct($input)
    {
        $collection = new AriaAttributeCollection($input);

        // No exception should be thrown.
        $this->assertInstanceOf(AriaAttributeCollection::class, $collection);
        $this->assertEquals($input, $collection->getArrayCopy(true));
    }

    public function constructProvider()
    {
        return [
            [[Aria::CONTROLS => '_IDREF']],
            [[Aria::CONTROLS => 'IDREF1 IDREF2']],
            [[Aria::DESCRIBED_BY => '_IDREF']],
            [[Aria::DESCRIBED_BY => 'IDREF1 IDREF2']],
            [[Aria::FLOW_TO => '_IDREF']],
            [[Aria::FLOW_TO => 'IDREF1 IDREF2']],
            [[Aria::LABELLED_BY => '_IDREF']],
            [[Aria::LABELLED_BY => 'IDREF1 IDREF2']],
            [[Aria::OWNS => '_IDREF']],
            [[Aria::OWNS => 'IDREF1 IDREF2']],
            [[Aria::LEVEL => '20']],
            [[Aria::LIVE => 'polite']],
            [[Aria::ORIENTATION => 'horizontal']],
            [[Aria::LABEL => 'A normalized string']]
        ];
    }

    /**
     * @param $input
     * @param $expectedMsg
     * @dataProvider constructErrorProvider
     */
    public function testConstructError($input, $expectedMsg)
    {
        $this->setExpectedException(InvalidArgumentException::class, $expectedMsg);
        $collection = new AriaAttributeCollection($input);
    }

    public function constructErrorProvider()
    {
        return [
            [[Aria::CONTROLS => '999999'], "'999999' is not a valid value for attribute 'aria-controls'."],
            [[Aria::CONTROLS => 'ABCD 999999'], "'999999' is not a valid value for attribute 'aria-controls'."],
            [[Aria::DESCRIBED_BY => '999999'], "'999999' is not a valid value for attribute 'aria-describedby'."],
            [[Aria::DESCRIBED_BY => 'ABCD 999999'], "'999999' is not a valid value for attribute 'aria-describedby'."],
            [[Aria::FLOW_TO => '999999'], "'999999' is not a valid value for attribute 'aria-flowto'."],
            [[Aria::FLOW_TO => 'ABCD 999999'], "'999999' is not a valid value for attribute 'aria-flowto'."],
            [[Aria::LABELLED_BY => '999999'], "'999999' is not a valid value for attribute 'aria-labelledby'."],
            [[Aria::LABELLED_BY => 'ABCD 999999'], "'999999' is not a valid value for attribute 'aria-labelledby'."],
            [[Aria::OWNS => '999999'], "'999999' is not a valid value for attribute 'aria-owns'."],
            [[Aria::OWNS => 'ABCD 999999'], "'999999' is not a valid value for attribute 'aria-owns'."],
            [[Aria::LEVEL => '-5'], "'-5' is not a valid value for attribute 'aria-level'."],
            [[Aria::LIVE => 'impolite'], "'impolite' is not a valid value for attribute 'aria-live'."],
            [[Aria::LIVE => 'diagonal'], "'diagonal' is not a valid value for attribute 'aria-live'."],
            [[Aria::CONTROLS => false], "AriaAttributeCollection can only contain strings."],
        ];
    }

    /**
     * @param $enum
     * @param $value
     * @dataProvider offsetSetProvider
     */
    public function testOffsetSet($enum, $value)
    {
        $collection = new AriaAttributeCollection();
        $collection[$enum] = $value;

        $this->assertSame($value, $collection[$enum]);
    }

    public function offsetSetProvider()
    {
        return [
            [Aria::CONTROLS, '_IDREF'],
            [Aria::CONTROLS, 'IDREF1 IDREF2'],
            [Aria::DESCRIBED_BY, '_IDREF'],
            [Aria::DESCRIBED_BY, 'IDREF1 IDREF2'],
            [Aria::FLOW_TO, '_IDREF'],
            [Aria::FLOW_TO, 'IDREF1 IDREF2'],
            [Aria::LABELLED_BY, '_IDREF'],
            [Aria::LABELLED_BY, 'IDREF1 IDREF2'],
            [Aria::OWNS, '_IDREF'],
            [Aria::OWNS, 'IDREF1 IDREF2'],
            [Aria::LEVEL, '20'],
            [Aria::LIVE, 'polite'],
            [Aria::ORIENTATION, 'horizontal'],
            [Aria::LABEL, 'A normalized string.']
        ];
    }

    /**
     * @param $enum
     * @param $value
     * @param $expectedMsg
     * @dataProvider offsetSetErrorProvider
     */
    public function testOffsetSetError($enum, $value, $expectedMsg)
    {
        $this->setExpectedException(InvalidArgumentException::class, $expectedMsg);

        $collection = new AriaAttributeCollection();
        $collection[$enum] = $value;
    }

    public function offsetSetErrorProvider()
    {
        return [
            [Aria::CONTROLS, '999999', "'999999' is not a valid value for attribute 'aria-controls'."],
            [Aria::CONTROLS, 'ABCD 999999', "'999999' is not a valid value for attribute 'aria-controls'."],
            [Aria::DESCRIBED_BY, '999999', "'999999' is not a valid value for attribute 'aria-describedby'."],
            [Aria::DESCRIBED_BY, 'ABCD 999999', "'999999' is not a valid value for attribute 'aria-describedby'."],
            [Aria::FLOW_TO, '999999', "'999999' is not a valid value for attribute 'aria-flowto'."],
            [Aria::FLOW_TO, 'ABCD 999999', "'999999' is not a valid value for attribute 'aria-flowto'."],
            [Aria::LABELLED_BY, '999999', "'999999' is not a valid value for attribute 'aria-labelledby'."],
            [Aria::LABELLED_BY, 'ABCD 999999', "'999999' is not a valid value for attribute 'aria-labelledby'."],
            [Aria::OWNS, '999999', "'999999' is not a valid value for attribute 'aria-owns'."],
            [Aria::OWNS, 'ABCD 999999', "'999999' is not a valid value for attribute 'aria-owns'."],
            [Aria::LEVEL, '-5', "'-5' is not a valid value for attribute 'aria-level'."],
            [Aria::LIVE, 'impolite', "'impolite' is not a valid value for attribute 'aria-live'."],
            [Aria::LIVE, 'diagonal', "'diagonal' is not a valid value for attribute 'aria-live'."],
            [Aria::CONTROLS, false, "AriaAttributeCollection can only contain strings."],
        ];
    }
}
