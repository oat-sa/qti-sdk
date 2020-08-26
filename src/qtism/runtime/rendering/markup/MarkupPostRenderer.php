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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup;

use qtism\runtime\rendering\Renderable;
use qtism\runtime\rendering\RenderingException;
use qtism\runtime\rendering\markup\Utils;

/**
 * Class MarkupPostRenderer
 */
class MarkupPostRenderer implements Renderable
{
    /**
     * Whether or not format the XML output.
     *
     * @var bool
     */
    private $formatOutput = false;

    /**
     * Whether or not clean up XML declarations.
     *
     * @var bool
     */
    private $cleanUpXmlDeclaration = false;

    /**
     * Whether or not transforms template statements
     * into PHP statements.
     *
     * @var bool
     */
    private $templateOriented = false;

    /**
     * Template fragments generated during the last invokation of ::render().
     *
     * @var array
     */
    private $fragments;

    /**
     * Template fragments path prefix.
     *
     * @var string
     */
    private $fragmentPrefix = '';

    /**
     * Create a new MarkupPostRenderer object.
     *
     * @param bool $formatOutput Whether or not format the XML output.
     * @param bool $cleanUpXmlDeclaration Whether or not clean up XML declaration (i.e. <?xml version="1.0" ... ?>).
     * @param bool $templateOriented Whether or not replace qtism control statements (e.g. qtism-if, qtism-endif) or qtism functions (e.g. qtism-printedVariable) into PHP control statements/function calls.
     */
    public function __construct($formatOutput = false, $cleanUpXmlDeclaration = false, $templateOriented = false)
    {
        $this->formatOutput($formatOutput);
        $this->cleanUpXmlDeclaration($cleanUpXmlDeclaration);
        $this->templateOriented($templateOriented);
    }

    /**
     * Set whether or not to format the XML output.
     *
     * @param bool $formatOutput
     */
    public function formatOutput($formatOutput)
    {
        $this->formatOutput = $formatOutput;
    }

    /**
     * Whether or not the XML output will be formatted.
     *
     * @return bool
     */
    public function mustFormatOutput()
    {
        return $this->formatOutput;
    }

    /**
     * Set whether or not XML declarations must
     * be clean up.
     *
     * @param bool $cleanUpXmlDeclaration
     */
    public function cleanUpXmlDeclaration($cleanUpXmlDeclaration)
    {
        $this->cleanUpXmlDeclaration = $cleanUpXmlDeclaration;
    }

    /**
     * Whether or not XML declarations must be clean up.
     *
     * @return bool
     */
    public function mustCleanUpXmlDeclaration()
    {
        return $this->cleanUpXmlDeclaration;
    }

    /**
     * Set whether or not template statements (qtism-if,  qtism-endif, ...)
     * must be transformed into PHP statements.
     *
     * @param bool $templateOriented
     */
    public function templateOriented($templateOriented)
    {
        $this->templateOriented = $templateOriented;
    }

    /**
     * Whether or not template statements (qtism-if, qtism-endif, ...)
     * must be transformed into PHP statements.
     *
     * @return bool
     */
    public function isTemplateOriented()
    {
        return $this->templateOriented;
    }

    /**
     * Get the template fragments generated during the last invokation of the ::render() method.
     *
     * The returned array is composed of arrays with the following keys:
     *
     * * path: the path relative to the rendered file where the fragment should be stored.
     * * content: the content of the fragment.
     *
     * @return array
     */
    public function getFragments()
    {
        return $this->fragments;
    }

    /**
     * Set the template fragments generated during the last invokation of the ::render() method.
     *
     * @param array $fragments
     */
    protected function setFragments(array $fragments)
    {
        $this->fragments = $fragments;
    }

    /**
     * Set the prefix to be used for fragment file names.
     *
     * @param string $fragmentPrefix .
     */
    public function setFragmentPrefix($fragmentPrefix)
    {
        $this->fragmentPrefix = $fragmentPrefix;
    }

    /**
     * Get the prefix to be used for fragment file names.
     */
    protected function getFragmentPrefix()
    {
        return $this->fragmentPrefix;
    }

    /**
     * @param mixed $document
     * @return mixed|string|string[]|null
     * @throws RenderingException
     */
    public function render($document)
    {
        if ($document->documentElement === null) {
            $msg = 'The XML Document to be rendered has no root element (i.e. it is empty).';
            throw new RenderingException($msg, RenderingException::RUNTIME);
        }

        $this->setFragments([]);

        /*
         * 1. Format the output.
         */
        $oldFormatOutput = $document->formatOutput;
        if ($this->mustFormatOutput()) {
            $document->formatOutput = true;
        }

        $output = @$document->saveXML();

        if ($output === false) {
            $document->formatOutput = $oldFormatOutput;
            $msg = 'A PHP internal error occurred while rendering the XML Document.';
            throw new RenderingException($msg, RenderingException::RUNTIME);
        }

        /*
         * 2. Transform qtism-if, qtism-printVariable, qtism-include statements
         * into PHP statements.
         */
        if ($this->isTemplateOriented() === true) {
            $output = preg_replace('/<!--\s+qtism-if\s*\((.+?)\)\s*:\s+-->/iu', '<?php if (\1): ?>', $output);
            $output = preg_replace('/<!--\s+qtism-endif\s+-->/iu', '<?php endif; ?>', $output);

            $className = Utils::class;
            $call = "<?php echo ${className}::printVariable(\\1); ?>";
            $output = preg_replace('/<!--\s+qtism-printVariable\((.+?)\)\s+-->/iu', $call, $output);

            $matches = [];
            $fragmentPrefix = $this->getFragmentPrefix();
            if (($c = preg_match_all('/<!--\s+(?:qtism-include)\s*\((?:(\$.+?), ([0-9]+), "(.+?)", ([0-9]+?))\)\s*:\s+-->(.+?)<!--\s+qtism-endinclude\s+-->/ius', $output, $matches)) > 0) {
                $fragments = $this->getFragments();
                for ($i = 0; $i < $c; $i++) {
                    $output = str_replace($matches[0][$i], '<?php include(dirname(__FILE__) . "/' . $fragmentPrefix . $matches[2][$i] . '-" . ' . $matches[1][$i] . '->getShuffledChoiceIdentifierAt(' . $matches[2][$i] . ', ' . $matches[4][$i] . ') . ".phtml"); ?>', $output);
                    $fragments[] = [
                        'path' => $fragmentPrefix . $matches[2][$i] . '-' . $matches[3][$i] . '.phtml',
                        'content' => $matches[5][$i],
                    ];
                }
                $this->setFragments($fragments);
            }
        }

        /*
         * 3. Clean-up XML Declaration if requested.
         */
        if ($this->mustCleanUpXmlDeclaration() === true) {
            $output = preg_replace('/<\?xml.+?\?>\s*/iu', '', $output);
        }

        $document->formatOutput = $oldFormatOutput;

        return $output;
    }
}
