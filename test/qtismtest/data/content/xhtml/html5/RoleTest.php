<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\html5\Role;
use qtismtest\QtiSmEnumTestCase;

class RoleTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Role::class;
    }

    protected function getNames()
    {
        return [
            'article',
            'button',
            'checkbox',
            'columnheader',
            'complementary',
            'contentinfo',
            'definition',
            'directory',
            'document',
            'gridcell',
            'group',
            'heading',
            'img',
            'link',
            'list',
            'listbox',
            'listitem',
            'log',
            'math',
            'note',
            'option',
            'presentation',
            'radio',
            'radiogroup',
            'region',
            'row',
            'rowgroup',
            'rowheader',
            'separator',
            'slider',
            'spinbutton',
            'status',
            'tab',
            'tablist',
            'tabpanel',
            'textbox',
            'timer',
            'toolbar',
        ];
    }

    protected function getKeys()
    {
        return [
            'article',
            'button',
            'checkbox',
            'columnheader',
            'complementary',
            'contentinfo',
            'definition',
            'directory',
            'document',
            'gridcell',
            'group',
            'heading',
            'img',
            'link',
            'list',
            'listbox',
            'listitem',
            'log',
            'math',
            'note',
            'option',
            'presentation',
            'radio',
            'radiogroup',
            'region',
            'row',
            'rowgroup',
            'rowheader',
            'separator',
            'slider',
            'spinbutton',
            'status',
            'tab',
            'tablist',
            'tabpanel',
            'textbox',
            'timer',
            'toolbar',
        ];
    }

    protected function getConstants()
    {
        return [
             Role::ARTICLE,
             Role::BUTTON,
             Role::CHECKBOX,
             Role::COLUMN_HEADER,
             Role::COMPLEMENTARY,
             Role::CONTENT_INFO,
             Role::DEFINITION,
             Role::DIRECTORY,
             Role::DOCUMENT,
             Role::GRID_CELL,
             Role::GROUP,
             Role::HEADING,
             Role::IMG,
             Role::LINK,
             Role::LIST,
             Role::LIST_BOX,
             Role::LIST_ITEM,
             Role::LOG,
             Role::MATH,
             Role::NOTE,
             Role::OPTION,
             Role::PRESENTATION,
             Role::RADIO,
             Role::RADIO_GROUP,
             Role::REGION,
             Role::ROW,
             Role::ROW_GROUP,
             Role::ROW_HEADER,
             Role::SEPARATOR,
             Role::SLIDER,
             Role::SPIN_BUTTON,
             Role::STATUS,
             Role::TAB,
             Role::TAB_LIST,
             Role::TAB_PANEL,
             Role::TEXT_BOX,
             Role::TIMER,
             Role::TOOLBAR,
        ];
    }
}
