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
namespace qtism\cli;

use cli\Arguments;

/**
 * The main class of the Command Line Interface.
 * 
 * The Cli class represents a CLI Module (e.g. render, validate, ...) that will be triggered
 * through the command line.
 * 
 * Some components of this class are inspired by Sebastian Bergmann's PHPUnit command line (BSD-3-Clause). 
 * Thanks to him for his great devotion to the PHP community.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class Cli
{
    const EXIT_SUCCESS = 0;
    const EXIT_FAILURE = 1;
    
    /**
     * An Arguments object (from php-cli-tools) representing the input
     * arguments of the CLI module.
     * 
     * @var \cli\Arguments
     * @see https://github.com/wp-cli/php-cli-tools The PHP CLI Tools github repository.
     */
    private $arguments;
    
    /**
     * Main CLI entry point.
     */
    public static function main()
    {
        $cli = new static();
        
        // Initialize arguments from factory method.
        $arguments = $cli->setupArguments();
        $arguments->parse();
        $cli->setArguments($arguments);
        
        return $cli->run();
    }
    
    /**
     * Run the Command Line Interface.
     */
    abstract protected function run();
    
    /**
     * Setup the arguments of the CLI Module.
     * 
     * @return \cli\Arguments An Arguments object (from php-cli-tools).
     * @see https://github.com/wp-cli/php-cli-tools The PHP CLI Tools github repository.
     */
    abstract protected function setupArguments();
    
    /**
     * Set the parsed arguments to the CLI Module.
     * 
     * @param cli\Arguments $arguments An Arguments object from php-cli-tools.
     */
    private function setArguments(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }
    
    /**
     * Get the parsed arguments of the current CLI Module.
     * 
     * @return \cli\Arguments An Arguments object from php-cli-tools.
     */
    protected function getArguments()
    {
        return $this->arguments;
    }
    
    /**
     * Show an error message as a single line.
     * 
     * @param string $message The error message.
     */
    protected function error($message)
    {
        echo "${message}\n";
    }
    
    /**
     * Show an error message as a single line and return a non zero exit status.
     * 
     * @param string $message The error message.
     */
    protected function fail($message)
    {
        $this->error($message);
        exit(self::EXIT_FAILURE);
    }
}