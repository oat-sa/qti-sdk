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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */

namespace qtism\common\datatypes\files;

/**
 * This interface represents the AbstractFactory interface of the 
 * AbstractFactory Design Pattern.
 * 
 * The FileFactory interface aims at providing a common interface
 * to create new instances of File (the Product in the context of AbstractFactory
 * Design Pattern) in various flavours.
 * 
 * @author Jérôme Bogaerts <jerome@taotesing.com>
 * @see http://en.wikipedia.org/wiki/Abstract_factory_pattern The Abstract Factory Design Pattern.
 */
interface FileFactory {
    
    /**
     * Instantiate an implementation of File which focuses on
     * keeping a file in memory.
     * 
     * @param string $data The data composing the file.
     * @param string $mimeType The MIME type of the file.
     * @param string $filename An optional file name.
     */
    public function createMemoryFile($data, $mimeType, $filename = '');
}