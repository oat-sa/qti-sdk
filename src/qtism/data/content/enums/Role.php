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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\enums;

use qtism\common\enums\AbstractEnumeration;

/**
 * The Html5 ARIA role enumeration.
 * Roles are defined and described by their characteristics.
 * Characteristics define the structural function of a role, such as what a
 * role is, concepts behind it, and what instances the role can or must contain.
 */
class Role extends AbstractEnumeration
{
    /**
     * A section of a page that consists of a composition that forms an
     * independent part of a document, page, or site.
     */
    private const ARTICLE = 1;

    /**
     * An input that allows for user-triggered actions when clicked or pressed.
     * See related link.
     */
    private const BUTTON = 2;

    /**
     * A checkable input that has three possible values: true, false, or mixed.
     */
    private const CHECKBOX = 3;

    /**
     * A cell containing header information for a column.
     */
    private const COLUMN_HEADER = 4;

    /**
     * A supporting section of the document, designed to be complementary to
     * the main content at a similar level in the DOM hierarchy, but remains
     * meaningful when separated from the main content.
     */
    private const COMPLEMENTARY = 5;

    /**
     * A large perceivable region that contains information about the parent
     * document.
     */
    private const CONTENT_INFO = 6;

    /**
     * A definition of a term or concept.
     */
    private const DEFINITION = 7;

    /**
     * A list of references to members of a group, such as a static table of
     * contents.
     */
    private const DIRECTORY = 8;

    /**
     * A region containing related information that is declared as document
     * content, as opposed to a web application.
     */
    private const DOCUMENT = 9;

    /**
     * A cell in a grid or treegrid.
     */
    private const GRID_CELL = 10;

    /**
     * A set of user interface objects which are not intended to be included
     * in a page summary or table of contents by assistive technologies.
     */
    private const GROUP = 11;

    /**
     * A heading for a section of the page.
     */
    private const HEADING = 12;

    /**
     * A container for a collection of elements that form an image.
     */
    private const IMG = 13;

    /**
     * An interactive reference to an internal or external resource that, when
     * activated, causes the user agent to navigate to that resource.
     * See related button.
     */
    private const LINK = 14;

    /**
     * A group of non-interactive list items. See related listbox.
     */
    private const LIST = 15;

    /**
     * A widget that allows the user to select one or more items from a list of
     * choices. See related combobox and list.
     */
    private const LIST_BOX = 16;

    /**
     * A single item in a list or directory.
     */
    private const LIST_ITEM = 17;

    /**
     * A type of live region where new information is added in meaningful order
     * and old information may disappear. See related marquee.
     */
    private const LOG = 18;

    /**
     * Content that represents a mathematical expression.
     */
    private const MATH = 19;

    /**
     * A section whose content is parenthetic or ancillary to the main content
     * of the resource.
     */
    private const NOTE = 20;

    /**
     * A selectable item in a select list.
     */
    private const OPTION = 21;

    /**
     * An element whose implicit native role semantics will not be mapped to
     * the accessibility API.
     */
    private const PRESENTATION = 22;

    /**
     * A checkable input in a group of radio roles, only one of which can be
     * checked at a time.
     */
    private const RADIO = 23;

    /**
     * A group of radio buttons.
     */
    private const RADIO_GROUP = 24;

    /**
     * A large perceivable section of a web page or document, that is important
     * enough to be included in a page summary or table of contents, for
     * example, an area of the page containing live sporting event statistics.
     */
    private const REGION = 25;

    /**
     * A row of cells in a grid.
     */
    private const ROW = 26;

    /**
     * A group containing one or more row elements in a grid.
     */
    private const ROW_GROUP = 27;

    /**
     * A cell containing header information for a row in a grid.
     */
    private const ROW_HEADER = 28;

    /**
     * A divider that separates and distinguishes sections of content or groups
     * of menuitems.
     */
    private const SEPARATOR = 29;

    /**
     * A user input where the user selects a value from within a given range.
     */
    private const SLIDER = 30;

    /**
     * A form of range that expects the user to select from among discrete
     * choices.
     */
    private const SPIN_BUTTON = 31;

    /**
     * A container whose content is advisory information for the user but is
     * not important enough to justify an alert, often but not necessarily
     * presented as a status bar. See related alert.
     */
    private const STATUS = 32;

    /**
     * A grouping label providing a mechanism for selecting the tab content
     * that is to be rendered to the user.
     */
    private const TAB = 33;

    /**
     * A list of tab elements, which are references to tabpanel elements.
     */
    private const TAB_LIST = 34;

    /**
     * A container for the resources associated with a tab, where each tab is
     * contained in a tablist.
     */
    private const TAB_PANEL = 35;

    /**
     * Input that allows free-form text as its value.
     */
    private const TEXT_BOX = 36;

    /**
     * A type of live region containing a numerical counter which indicates an
     * amount of elapsed time from a start point, or the time remaining until
     * an end point.
     */
    private const TIMER = 37;

    /**
     * A collection of commonly used function buttons or controls represented
     * in compact visual form.
     */
    private const TOOLBAR = 38;

    public static function asArray(): array
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

    public static function getDefault(): ?int
    {
        return null;
    }
}
