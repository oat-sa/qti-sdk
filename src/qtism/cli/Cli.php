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

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

/**
 * The main class of the Command Line Interface.
 * 
 * Some components of this class are inspired by Sebastian Bergmann's PHPUnit command line (BSD-3-Clause). 
 * Thanks to him for his great devotion to the PHP community.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Cli
{
    const EXIT_SUCCESS = 0;
    const EXIT_FAILURE = 1;
    
    /**
     * Main CLI entry point.
     */
    public static function main()
    {
        $cli = new static();
        
        return $cli->run($_SERVER['argv']);
    }
    
    /**
     * Run the Command Line Interface for a given set of arguments.
     * 
     * @param array $argv The Command Line Arguments.
     */
    protected function run(array $argv)
    {
        $this->runRender($argv);
        exit(self::EXIT_SUCCESS);
    }
    
    private function runRender(array $argv)
    {
        $renderer = new XhtmlRenderingEngine();
        $source = $argv[2];
        
        if (is_readable($source) === true) {
            $doc = new XmlDocument();
            $doc->load($source);
            
            $xml = $renderer->render($doc->getDocumentComponent());
            $xml->formatOutput = true;
            
            $header = "<!doctype html>\n";
            $header .= "<html>\n";
            $header .= "<head>\n";
            $header .= "<meta charset=\"utf-8\">\n";
            $header .= "</head>\n";
            $header .= "<body>\n";
            
            $body = $xml->saveXml($xml->documentElement) . "\n";
            
            $footer = "</body>\n";
            $footer .= "</html>\n";
            echo "{$header}{$body}{$footer}";
        }
    }
    
    private function showError($message)
    {
        echo "${message}\n";
        exit(self::EXIT_FAILURE);
    }
}