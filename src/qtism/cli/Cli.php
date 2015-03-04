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
use cli as CliTools;

/**
 * The main class of the Command Line Interface.
 * 
 * The Cli class represents a CLI Module (e.g. render, validate, ...) that will be triggered
 * through the command line.
 * 
 * If the -v/--verbose flag is found in the CLI command, the verbose mode will be turned on. 
 * \qtism\cli\Cli::info() and \qtism\cli\Cli::success() output methods will behave differently
 * whether the verbose mode is turned on/off. When the verbose mode is turned on, messages
 * associated with these method will display in stdout. Otherwise, nothing will be displayed
 * in stdout.
 * 
 * However, the \qtism\cli\Cli::error() and \qtism\cli\Cli::fail() and \qtism\cli\Cli::missingArgument() 
 * methods will always write messages in stderr, even if the verbose mode is turned off.
 * 
 * If one or more command line arguments is missing/invalid, Cli implementations will display
 * an appropriate error message and return a zero exit status.
 * 
 * To make CLI Module commands more homogeneous, they will respect the following rules:
 * 
 * * Flags have a long name, and a unique alias (e.g. --format, -f).
 * * Options have a long name only (e.g. --source).
 * 
 * Some components of this class are inspired by Sebastian Bergmann's PHPUnit command line (BSD-3-Clause). 
 * Thanks to him for his great devotion to the PHP community.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class Cli
{
    /**
     * POSIX success exit status (0).
     * 
     * @var integer
     */
    const EXIT_SUCCESS = 0;
    
    /**
     * POSIX generic exit status (1).
     * 
     * Implementations are free to use more appropriate
     * non zero exit status codes if appropriate.
     * 
     * @var integer
     */
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
        
        // Add help flag.
        $arguments->addFlag(array('help', 'h'), 'Show help screen.');
        
        // Add verbose flag.
        $arguments->addFlag(array('verbose', 'v'), 'Verbose mode.');
        
        // Parse arguments and provide to implementation.
        $arguments->parse();
        $cli->setArguments($arguments);
        
        if ($arguments['help'] === true) {
            echo $arguments->getHelpScreen() . "\n\n";
        } else {
            // Perform arguments check.
            $cli->checkArguments();
            
            // Run the CLI Module implementation.
            $cli->run();
        }
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
     * Check the arguments given to the CLI Module.
     * 
     * It is the responsibility of the implementer to check the arguments
     * and their values. If any of the arguments is missing or contains
     * invalid data, it must display an appropriate message and terminate
     * the execution of the CLI Module by calling \qtism\cli\Cli::fail()
     * method.
     * 
     * @see \qtism\cli\Cli::fail()
     */
    abstract protected function checkArguments();
    
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
     * Show an error message as a single line in stderr.
     * 
     * Error messages will be shown even if the verbose mode is not in force.
     * 
     * @param string $message The error message.
     */
    protected function error($message)
    {
        $this->out("%r${message}%n", true);
    }
    
    /**
     * Show a success message as a single line in stdout and return a zero exit status.
     * 
     * This method produces no output if verbose mode is not in force but the exit status
     * will still be returned.
     * 
     * @param string $message The success message.
     */
    protected function success($message)
    {
        if ($this->isVerbose() === true) {
            $this->out("%g${message}%n", true);
        }
        
        exit(self::EXIT_SUCCESS);
    }
    
    /**
     * Show an error message as a single line and return a non zero exit status.
     * 
     * The $message will go in stderr even if the verbose mode is not in force. 
     * 
     * @param string $message The error message.
     */
    protected function fail($message)
    {
        $this->error($message);
        exit(self::EXIT_FAILURE);
    }
    
    protected function missingArgument($longName)
    {
        $arguments = $this->getArguments();
        $options = $arguments->getOptions();
        
        $msg = "Missing argument";
        
        if (array_key_exists($longName, $options)) {
            $msg .= " '${longName}'";
        }
        
        $msg .= ".";
        
        $this->error($msg);
        $this->fail("Use the --help option to see the help screen.");
    }
    
    /**
     * Show an information message as a single line.
     * 
     * This method produces no output if verbose mode is not in force.
     * 
     * @param string $message An information message.
     */
    protected function info($message)
    {
        if ($this->isVerbose() === true) {
            $this->out("%w${message}%n", true);
        }
    }
    
    /**
     * Show raw data in console even if verbose mode is not in force.
     * 
     * @param string $data The data to go in output.
     * @param boolean $newLine Whether to display a new line after $data.
     */
    protected function out($data, $newLine = true)
    {
        CliTools\out($data);
        
        if ($newLine === true) {
            CliTools\out("\n");
        }
    }
    
    /**
     * Check wheter the verbose mode is in force.
     * 
     * The verbose mode is in force if the CLI arguments contain
     * the -h/--help flag.
     * 
     * @return boolean
     */
    protected function isVerbose()
    {
        $arguments = $this->getArguments();
        return $this->arguments['verbose'] === true;
    }
}