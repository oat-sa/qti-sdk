<?php

namespace qtismtest\data\content\xhtml\tables;

use qtism\data\content\xhtml\tables\TableCellScope;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class TableCellScopeTest
 */
class TableCellScopeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return TableCellScope::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'row',
            'col',
            'rowgroup',
            'colgroup',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'ROW',
            'COL',
            'ROWGROUP',
            'COLGROUP',
        ];
    }

    /**
     * @return array
     */
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
