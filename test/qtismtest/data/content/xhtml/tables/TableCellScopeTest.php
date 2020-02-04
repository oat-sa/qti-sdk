<?php

namespace qtismtest\data\content\xhtml\tables;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\content\xhtml\tables\TableCellScope;

class TableCellScopeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TableCellScope::class;
    }
    
    protected function getNames()
    {
        return array(
            'row',
            'col',
            'rowgroup',
            'colgroup'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'ROW',
            'COL',
            'ROWGROUP',
            'COLGROUP'
        );
    }
    
    protected function getConstants()
    {
        return array(
            TableCellScope::ROW,
            TableCellScope::COL,
            TableCellScope::ROWGROUP,
            TableCellScope::COLGROUP,
        );
    }
}
