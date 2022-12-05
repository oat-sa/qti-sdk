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
 * Copyright (c) 2014-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\datatypes\files;

use qtism\common\datatypes\QtiFile;

/**
 * The File Management System of QTISM is an augmented implementation
 * of AbstractFactory. In addition to focusing on the creation of various
 * implementations of the File interface, it also provides a way to delete
 * created Files.
 *
 * This interface represents the AbstractFactory interface of the
 * AbstractFactory Design Pattern.
 *
 * @see http://en.wikipedia.org/wiki/Abstract_factory_pattern The Abstract Factory Design Pattern.
 */
interface FileManager
{
    /**
     * Instantiate an implementation of File which focuses
     * on keeping a file on the file system from an existing file.
     *
     * @param string $path The path to the file containing the data of the returned File object.
     * @param string $mimeType The MIME type of the resulting File object.
     * @param string $filename The filename of the resulting File object.
     * @return QtiFile
     * @throws FileManagerException
     */
    public function createFromFile($path, $mimeType, $filename = ''): QtiFile;

    /**
     * Instantiate an implementation of File which focuses
     * on keeping a file on the file system from a $data binary string.
     *
     * @param string $data A binary string representing the data.
     * @param string $mimeType The MIME type of the resulting File object.
     * @param string $filename The filename of the resulting File object.
     * @param string|null $path A path for file provided externally of the resulting File object
     * @return QtiFile
     * @throws FileManagerException
     */
    public function createFromData($data, $mimeType, $filename = '', $path = null): QtiFile;

    /**
     * Retrieve a previously created instance by $identifier.
     *
     * @param string $identifier
     * @param string|null $filename
     * @throws FileManagerException
     */
    public function retrieve($identifier, $filename = null);

    /**
     * Delete a given QtiFile from its storage.
     *
     * @param QtiFile $file A persistent file to be deleted gracefully.
     * @throws FileManagerException
     */
    public function delete(QtiFile $file);
}
