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
 * The File Management System of QTISM is an augmented implementation
 * of AbstractFactory. In addition to focusing on the creation of various
 * implementations of the File interface, it also provides a way to delete
 * created Files.
 * 
 * This interface represents the AbstractFactory interface of the 
 * AbstractFactory Design Pattern.
 * 
 * @author Jérôme Bogaerts <jerome@taotesing.com>
 * @see http://en.wikipedia.org/wiki/Abstract_factory_pattern The Abstract Factory Design Pattern.
 */
interface FileManager {
    
    /**
     * Instantiate an implementation of File which focuses on
     * keeping a file in memory.
     * 
     * @param string $data The data composing the file.
     * @param string $mimeType The MIME type of the file.
     * @param string $filename An optional file name.
     * @throws FileManagerException
     */
    public function createMemoryFile($data, $mimeType, $filename = '');
    
    /**
     * Instantiate an implementation of File which focuses
     * on keeping a file on the file system.
     * 
     * @param string $path The path to the file containing the data of the returned File object.
     * @param string $mimeType The MIME type of the resulting File object.
     * @param string $filename The filename of the resulting File object.
     * @return AbstractPersistentFile
     * @throws FileManagerException
     */
    public function createPersistentFile($path, $mimeType, $filename = '');
    
    /**
     * Delete a given AbstractPersistentFile from its storage media.
     * 
     * @param AbstractPersistentFile $file A persistent file to be deleted gracefully.
     * @throws FileManagerException
     */
    public function deletePersistentFile(AbstractPersistentFile $file);
}