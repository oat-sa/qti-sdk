<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\storage\xml\versions\QtiVersion;
use \qtism\data\storage\xml\marshalling\Marshaller;
use DOMElement;
use DOMException;

trait QtiNamespacePrefixTrait
{
    /**
     * @return string
     */
    abstract public function getVersion(): string;

    /**
     * @return DOMElement|false
     * @throws DOMException
     */
    public function getNamespacedElement(QtiComponent $component)
    {
        $prefix = $component->getTargetNamespacePrefix();
        $version = QtiVersion::create($this->getVersion());
        $namespace = $version->getExternalNamespace($prefix);

        return Marshaller::getDOMCradle()->createElementNS(
            $namespace,
            $prefix . ':' . $component->getQtiClassName()
        );
    }
}
