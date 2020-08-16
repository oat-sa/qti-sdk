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

namespace qtism\data\storage\php\marshalling;

use qtism\common\collections\AbstractCollection;
use qtism\common\storage\StreamAccessException;
use qtism\data\storage\php\PhpArgument;
use qtism\data\storage\php\PhpArgumentCollection;
use qtism\data\storage\php\PhpVariable;

/**
 * Implements the logic of marshalling AbstractCollection objects into
 * PHP source code.
 */
class PhpCollectionMarshaller extends PhpMarshaller
{
    /**
     * Marshall AbstractCollection objects into PHP source code.
     *
     * @throws PhpMarshallingException If something wrong happens during marshalling.
     */
    public function marshall()
    {
        $collection = $this->getToMarshall();
        $ctx = $this->getContext();
        $access = $ctx->getStreamAccess();

        $valueArray = $collection->getArrayCopy();
        $valueArrayVarName = $ctx->generateVariableName($valueArray);

        $arrayArgs = new PhpArgumentCollection();
        if (($count = count($collection)) > 0) {
            foreach ($ctx->popFromVariableStack($count) as $itemName) {
                $arrayArgs[] = new PhpArgument(new PhpVariable($itemName));
            }
        }

        try {
            $access->writeVariable($valueArrayVarName);
            $access->writeEquals($ctx->mustFormatOutput());
            $access->writeFunctionCall('array', $arrayArgs);
            $access->writeSemicolon($ctx->mustFormatOutput());

            $collectionVarName = $ctx->generateVariableName($collection);
            $access->writeVariable($collectionVarName);
            $access->writeEquals($ctx->mustFormatOutput());
            $collectionArgs = new PhpArgumentCollection([new PhpArgument(new PhpVariable($valueArrayVarName))]);
            $access->writeInstantiation(get_class($collection), $collectionArgs);
            $access->writeSemicolon($ctx->mustFormatOutput());

            $ctx->pushOnVariableStack($collectionVarName);
        } catch (StreamAccessException $e) {
            $msg = 'An error occurred while marshalling a collection into PHP source code.';
            throw new PhpMarshallingException($msg, PhpMarshallingException::STREAM, $e);
        }
    }

    /**
     * Whether the $toMarshall value is marshallable by this implementation which
     * only supports AbstractCollection objects to be marshalled.
     *
     * @return boolean
     */
    protected function isMarshallable($toMarshall)
    {
        return $toMarshall instanceof AbstractCollection;
    }
}
