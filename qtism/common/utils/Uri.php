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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */
namespace qtism\common\utils;

/**
 * A utility class focusing on URIs (Uniform Resource Identifier).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Uri {
    
    /**
     * Remove leading and trailing slashes (/) and whitespaces (\t, \n, \r, \0, \x0B)  from a given URI
     * or $uriComponent.
     * 
     * @return The trimmed URI or URI component.
     */
    static public function trim($uriComponent) {
        // Trim is UTF-8 safe if the second argument does not
        // contain multi-byte chars.
        return trim($uriComponent, "/\t\n\r\0\x0B");
    }
    
    /**
     * Remove leading slashes (/) and whitespaces (\t, \n, \r, \0, \x0B)  from a given URI
     * or $uriComponent.
     *
     * @return The trimmed URI or URI component.
     */
    static public function ltrim($uriComponent) {
        return ltrim($uriComponent, "/\t\n\r\0\x0B");
    }
    
    /**
     * Remove trailing slashes (/) and whitespaces (\t, \n, \r, \0, \x0B)  from a given URI
     * or $uriComponent.
     *
     * @return The trimmed URI or URI component.
     */
    static public function rtrim($uriComponent) {
        return rtrim($uriComponent, "/\t\n\r\0\x0B");
    }
}