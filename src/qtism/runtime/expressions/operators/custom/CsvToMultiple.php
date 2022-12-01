<?php

namespace qti\customOperators;

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\CustomOperatorProcessor;

class CsvToMultiple extends CsvToContainer
{
    protected function createContainer()
    {
        return new MultipleContainer(BaseType::STRING);
    }
}
