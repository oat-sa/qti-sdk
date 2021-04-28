<?php

namespace qtismtest\data\content\enums;

use qtism\data\content\enums\Role;
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
        return $this->getNames();
    }

    protected function getConstants()
    {
        return array_map(
            [Role::class, 'getConstantByName'],
            $this->getNames()
        );
    }
}
