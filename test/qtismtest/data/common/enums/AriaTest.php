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

namespace qtismtest\data\content;

use qtism\data\common\enums\Aria;
use qtismtest\QtiSmEnumTestCase;

class AriaTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Aria::class;
    }

    protected function getNames()
    {
        return [
            'aria-controls',
            'aria-describedby',
            'aria-flowto',
            'aria-label',
            'aria-labelledby',
            'aria-level',
            'aria-live',
            'aria-orientation',
            'aria-owns'
        ];
    }

    protected function getKeys()
    {
        return [
            'CONTROLS',
            'DESCRIBED_BY',
            'FLOW_TO',
            'LABEL',
            'LABELLED_BY',
            'LEVEL',
            'LIVE',
            'ORIENTATION',
            'OWNS'
        ];
    }

    protected function getConstants()
    {
        return [
            Aria::CONTROLS,
            Aria::DESCRIBED_BY,
            Aria::FLOW_TO,
            Aria::LABEL,
            Aria::LABELLED_BY,
            Aria::LEVEL,
            Aria::LIVE,
            Aria::ORIENTATION,
            Aria::OWNS
        ];
    }
}
