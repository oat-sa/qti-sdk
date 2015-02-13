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
use qtism\data\storage\xml\Utils as XmlUtils;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;
use \DOMXPath;

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
        $profile = (isset($argv[3]) === true) ? $argv[3] : 'default';
        
        if (is_readable($source) === true) {
            $doc = new XmlDocument();
            $doc->load($source);
            
            $xml = $renderer->render($doc->getDocumentComponent());
            $xml->formatOutput = true;
            
            $header = "<!doctype html>\n";
            
            
            if (strtolower($profile) === 'aqti') {
                $xpath = new DOMXPath($xml);
                $assessmentItemElts = $xpath->query("//div[contains(@class, 'qti-assessmentItem')]");
                if ($assessmentItemElts->length > 0) {
                    $htmlAttributes = array();
                    
                    // Take the content of <assessmentItem> and put it into <html>.
                    $attributes = $assessmentItemElts->item(0)->attributes;
                    foreach ($attributes as $name => $attr) {
                        $htmlAttributes[] = $name . '="'. $attr->value . '"';
                    }
                    
                    while ($attributes->length > 0) {
                        $assessmentItemElts->item(0)->removeAttribute($attributes->item(0)->name);
                    }
                    
                    $header .= "<html " . implode(' ', $htmlAttributes) . ">\n";
                    $header .= "<head>\n";
                    $header .= "<meta charset=\"utf-8\">\n";
                    $header .= "</head>\n";
                    
                    $itemBodyElts = $xpath->query("//div[contains(@class, 'qti-itemBody')]");
                    if ($itemBodyElts->length > 0) {
                        $body = $xml->saveXml($itemBodyElts->item(0));
                        $body = substr($body, strlen('<div>'));
                        $body = substr($body, 0, strlen('</div>') * -1);
                        $body = "<body ${body}</body>\n";
                    } else {
                        $body = $xml->saveXml($xml->documentElement) . "\n";
                    }
                    
                    $footer = "</html>\n";
                }
            } else {
                $header .= "<html>\n";
                $header .= "<head>\n";
                $header .= "<meta charset=\"utf-8\">\n";
                $header .= "</head>\n";
                $header .= "<body>\n";
                
                $footer = "</body>\n";
                $footer .= "</html>\n";
                
                $body = $xml->saveXml($xml->documentElement) . "\n";
            }
            
            echo "{$header}{$body}{$footer}";
        }
    }
    
    private function showError($message)
    {
        echo "${message}\n";
        exit(self::EXIT_FAILURE);
    }
}