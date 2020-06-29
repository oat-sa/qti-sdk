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

namespace qtism\data\common\collections;

use InvalidArgumentException;
use qtism\common\collections\AbstractCollection;
use qtism\common\utils\Format;
use qtism\data\common\enums\Aria;

/**
 * Class AriaAttributeCollection.
 *
 * A specific Collection type aiming storing aria-* attributes and their values. Offsets to be
 * used with this Collction type must be values from the Aria Enumeration.
 *
 * @package qtism\data\common\collections
 */
class AriaAttributeCollection extends AbstractCollection
{

    /**
     * AriaAttributeCollection constructor.
     *
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        foreach ($array as $k => $a) {
            $this->offsetSet($k, $a);
        }

        reset($this->dataPlaceHolder);
    }

    /**
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    protected function checkType($value)
    {
        if (!is_string($value))  {
            throw new InvalidArgumentException("AriaAttributeCollection can only contain strings.");
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        $this->checkType($value);

        if (!in_array($offset, Aria::asArray())) {
            throw new InvalidArgumentException("The offset must be a value from the Aria enumeration.");
        }

        if (in_array($offset, [Aria::CONTROLS, Aria::DESCRIBED_BY, Aria::FLOW_TO, Aria::LABELLED_BY, Aria::OWNS])) {
            $ariaValues = explode("\x20", $value);
            foreach ($ariaValues as $ariaValue) {
                if (!Format::isIdentifier($ariaValue, false)) {
                    $msg = "'$ariaValue' is not a valid value for attribute '" . Aria::getNameByConstant($offset) . "'.";
                    throw new InvalidArgumentException($msg);
                }
            }

        } elseif ($offset === Aria::LEVEL) {
            if (!Format::isAriaLevel($value)) {
                $msg = "'" . intval($value) . "' is not a valid value for attribute 'aria-level'.";
                throw new InvalidArgumentException($msg);
            }
        } elseif ($offset === Aria::LIVE) {
            if (!Format::isAriaLive($value)) {
                $msg = "'${value}' is not a valid value for attribute 'aria-live'.";
                throw new InvalidArgumentException($msg);
            }

        } elseif ($offset === Aria::ORIENTATION) {
            if (!Format::isAriaOrientation($value)) {
                $msg = "'${value}' is not a valid value for attribute 'aria-orientation'.";
                throw new InvalidArgumentException($msg);
            }
        }

        $this->dataPlaceHolder[$offset] = strval($value);
    }
}