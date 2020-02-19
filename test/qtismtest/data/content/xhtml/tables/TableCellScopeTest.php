<?php

namespace qtismtest\data\content\xhtml\tables;

use qtism\data\content\xhtml\tables\TableCellScope;
use qtismtest\QtiSmEnumTestCase;

class TableCellScopeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TableCellScope::class;
    }

    protected function getNames()
    {
        return [
            'row',
            'col',
            'rowgroup',
            'colgroup',
        ];
    }

    protected function getKeys()
    {
        return [
            'ROW',
            'COL',
            'ROWGROUP',
            'COLGROUP',
        ];
    }

    protected function getConstants()
    {
        return [
            TableCellScope::ROW,
            TableCellScope::COL,
            TableCellScope::ROWGROUP,
            TableCellScope::COLGROUP,
        ];
    }
}
