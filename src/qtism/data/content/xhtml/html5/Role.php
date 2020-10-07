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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml\html5;

use qtism\common\enums\Enumeration;

/**
 * The Html5 role enumeration.
 */
class Role implements Enumeration
{
    const ARTICLE = 1;

    const BUTTON = 2;

    const CHECKBOX = 3;

    const COLUMN_HEADER = 4;

    const COMPLEMENTARY = 5;

    const CONTENT_INFO = 6;

    const DEFINITION = 7;

    const DIRECTORY = 8;

    const DOCUMENT = 9;

    const GRID_CELL = 10;

    const GROUP = 11;

    const HEADING = 12;

    const IMG = 13;

    const LINK = 14;

    const LIST = 15;

    const LIST_BOX = 16;

    const LIST_ITEM = 17;

    const LOG = 18;

    const MATH = 19;

    const NOTE = 20;

    const OPTION = 21;

    const PRESENTATION = 22;

    const RADIO = 23;

    const RADIO_GROUP = 24;

    const REGION = 25;

    const ROW = 26;

    const ROW_GROUP = 27;

    const ROW_HEADER = 28;

    const SEPARATOR = 29;

    const SLIDER = 30;

    const SPIN_BUTTON = 31;

    const STATUS = 32;

    const TAB = 33;

    const TAB_LIST = 34;

    const TAB_PANEL = 35;

    const TEXT_BOX = 36;

    const TIMER = 37;

    const TOOLBAR = 38;

    public static function asArray()
    {
        return [
            'article' => self::ARTICLE,
            'button' => self::BUTTON,
            'checkbox' => self::CHECKBOX,
            'columnheader' => self::COLUMN_HEADER,
            'complementary' => self::COMPLEMENTARY,
            'contentinfo' => self::CONTENT_INFO,
            'definition' => self::DEFINITION,
            'directory' => self::DIRECTORY,
            'document' => self::DOCUMENT,
            'gridcell' => self::GRID_CELL,
            'group' => self::GROUP,
            'heading' => self::HEADING,
            'img' => self::IMG,
            'link' => self::LINK,
            'list' => self::LIST,
            'listbox' => self::LIST_BOX,
            'listitem' => self::LIST_ITEM,
            'log' => self::LOG,
            'math' => self::MATH,
            'note' => self::NOTE,
            'option' => self::OPTION,
            'presentation' => self::PRESENTATION,
            'radio' => self::RADIO,
            'radiogroup' => self::RADIO_GROUP,
            'region' => self::REGION,
            'row' => self::ROW,
            'rowgroup' => self::ROW_GROUP,
            'rowheader' => self::ROW_HEADER,
            'separator' => self::SEPARATOR,
            'slider' => self::SLIDER,
            'spinbutton' => self::SPIN_BUTTON,
            'status' => self::STATUS,
            'tab' => self::TAB,
            'tablist' => self::TAB_LIST,
            'tabpanel' => self::TAB_PANEL,
            'textbox' => self::TEXT_BOX,
            'timer' => self::TIMER,
            'toolbar' => self::TOOLBAR,
        ];
    }

    public static function getConstantByName($name)
    {
        return self::asArray()[$name] ?? false;
    }

    public static function getNameByConstant($constant)
    {
        $constants = array_flip(self::asArray());

        return $constants[$constant] ?? false;
    }
}
