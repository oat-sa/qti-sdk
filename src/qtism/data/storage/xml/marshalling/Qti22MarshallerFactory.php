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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\common\utils\Reflection;
use ReflectionClass;
use qtism\data\storage\xml\marshalling\SimpleInlineMarshaller;

/**
 * A MarshallerFactory focusing on instantiating and configuring
 * Marshallers for QTI 2.2.
 */
class Qti22MarshallerFactory extends MarshallerFactory
{
    private const HTML5_NAMESPACE = 'http://www.imsglobal.org/xsd/imsqtiv2p2_html5_v1p0';
    
    /**
     * Create a new Qti22MarshallerFactory object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addMappingEntry('bdo', SimpleInlineMarshaller::class);
        $this->addMappingEntry('audio', AudioMarshaller::class, self::HTML5_NAMESPACE);
        $this->addMappingEntry('source', SourceMarshaller::class, self::HTML5_NAMESPACE);
        $this->addMappingEntry('track', TrackMarshaller::class, self::HTML5_NAMESPACE);
        $this->addMappingEntry('video', VideoMarshaller::class, self::HTML5_NAMESPACE);
    }

    /**
     * @param ReflectionClass $class
     * @param array $args
     * @return mixed
     */
    protected function instantiateMarshaller(ReflectionClass $class, array $args)
    {
        array_unshift($args, '2.2.0');
        return Reflection::newInstance($class, $args);
    }
}
