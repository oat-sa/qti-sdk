<?php

declare(strict_types=1);

namespace qtism\data\storage\xml;

interface QtiNamespaced
{
    public function getTargetNamespace(): string;
}
