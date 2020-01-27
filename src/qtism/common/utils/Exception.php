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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\utils;

/**
 * A utility class focusing on Exceptions.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Exception
{
    /**
     * Format an Exception message.
     *
     * This method will format an exception message using the following scheme:
     *
     * [CLASSNAME] MESSAGE
     *
     * If the Exception contains previous exceptions, the following scheme will be used:
     *
     * [CLASSNAME] MESSAGE
     * Caused by:
     * [CLASSNAME] MESSAGE
     * ...
     *
     * @param \Exception $e A PHP Exception object.
     * @param true $withClassName Whether to show the Exception class name.
     * @return string
     */
    static public function formatMessage(\Exception $e, $withClassName = true)
    {
        $returnValue = '';

        do {
            $className = get_class($e);
            $message = $e->getMessage();
            $returnValue .= ($withClassName === true) ? "[${className}] ${message}" : $message;

            if ($e = $e->getPrevious()) {
                $returnValue .= "\nCaused by:\n";
            }
        } while ($e);

        return $returnValue;
    }
}
