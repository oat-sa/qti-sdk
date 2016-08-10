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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\common\utils\Reflection;
use \ReflectionClass;

/**
 * A MarshallerFactory focusing on instantiating and configuring
 * Marshallers for QTI 2.1.0.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Qti21MarshallerFactory extends MarshallerFactory
{
    protected function instantiateMarshaller(ReflectionClass $class, array $args)
    {
        array_unshift($args, '2.1.0');
        return Reflection::newInstance($class, $args);
    }
}
