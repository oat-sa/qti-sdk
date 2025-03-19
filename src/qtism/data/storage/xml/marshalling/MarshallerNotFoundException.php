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

use Exception;

/**
 * Exception to be thrown when a Marshaller implementation cannot be found.
 */
class MarshallerNotFoundException extends Exception
{
    /**
     * A QTI class name e.g. 'assessmentItemRef'.
     *
     * @var string
     */
    private $qtiClassName;

    /**
     * Create a new MarshallerNotFoundException object.
     *
     * @param string $message A human readable message.
     * @param string $qtiClassName The QTI class name for which no Marshaller implementation could be find.
     * @param Exception $previous An optional previous caught Exception object.
     */
    public function __construct($message, $qtiClassName, ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->setQtiClassName($qtiClassName);
    }

    /**
     * Get the QTI class name for which no Marshaller implementation could be find.
     *
     * @return string
     */
    public function getQtiClassName(): string
    {
        return $this->qtiClassName;
    }

    /**
     * Set the QTI class name for which no Marshaller implementation could be find.
     *
     * @param string $qtiClassName
     */
    protected function setQtiClassName($qtiClassName): void
    {
        $this->qtiClassName = $qtiClassName;
    }
}
