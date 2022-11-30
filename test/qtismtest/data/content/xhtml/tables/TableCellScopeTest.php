<?php

declare(strict_types=1);

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
    protected function getEnumerationFqcn(): string
    {
        return TableCellScope::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
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
    protected function getKeys(): array
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
    protected function getConstants(): array
    {
        return [
            TableCellScope::ROW,
            TableCellScope::COL,
            TableCellScope::ROWGROUP,
            TableCellScope::COLGROUP,
        ];
    }
}
