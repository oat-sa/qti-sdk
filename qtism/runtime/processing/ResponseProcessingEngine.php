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

namespace qtism\runtime\processing;

use InvalidArgumentException;
use qtism\data\processing\ResponseProcessing;
use qtism\data\QtiComponent;
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\php\PhpStorageException;
use qtism\runtime\common\AbstractEngine;
use qtism\runtime\common\ProcessingCollectionException;
use qtism\runtime\common\ProcessingException;
use qtism\runtime\common\State;
use qtism\runtime\rules\RuleEngine;

/**
 * Class ResponseProcessingEngine
 */
class ResponseProcessingEngine extends AbstractEngine
{
    /**
     * An array used to map template URIs with actual location
     * of the templates. This array has keys containing the URL
     * of the template. The related values is the location of the
     * template to be used.
     *
     * @var array
     */
    private $templateMapping = [];

    /**
     * Create a new ResponseProcessingEngine object.
     *
     * @param QtiComponent $responseProcessing
     * @param State $context
     * @throws InvalidArgumentException If $responseProcessing is not a ResponseProcessing object.
     */
    public function __construct(QtiComponent $responseProcessing, State $context = null)
    {
        parent::__construct($responseProcessing, $context);

        $templateDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;

        // Response Processing Templates content are the same in QTI 2.1 and 2.2.
        $this->addTemplateMapping('http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct', $templateDir . '2_1' . DIRECTORY_SEPARATOR . 'match_correct.php');
        $this->addTemplateMapping('http://www.imsglobal.org/question/qti_v2p1/rptemplates/map_response', $templateDir . '2_1' . DIRECTORY_SEPARATOR . 'map_response.php');
        $this->addTemplateMapping('http://www.imsglobal.org/question/qti_v2p1/rptemplates/map_response_point', $templateDir . '2_1' . DIRECTORY_SEPARATOR . 'map_response_point.php');

        // Response Processing Templates differ from QTI 2.0 to QTI 2.1.
        $this->addTemplateMapping('http://www.imsglobal.org/question/qti_v2p0/rptemplates/match_correct', $templateDir . '2_0' . DIRECTORY_SEPARATOR . 'match_correct.php');
        $this->addTemplateMapping('http://www.imsglobal.org/question/qti_v2p0/rptemplates/map_response', $templateDir . '2_0' . DIRECTORY_SEPARATOR . 'match_correct.php');
        $this->addTemplateMapping('http://www.imsglobal.org/question/qti_v2p0/rptemplates/map_response_point', $templateDir . '2_0' . DIRECTORY_SEPARATOR . 'map_response_point.php');
    }

    /**
     * Set the ResponseProcessing object to be executed.
     *
     * @param QtiComponent $responseProcessing A ResponseProcessing object.
     * @throws InvalidArgumentException If $responseProcessing is not a ResponseProcessing object.
     */
    public function setComponent(QtiComponent $responseProcessing)
    {
        if ($responseProcessing instanceof ResponseProcessing) {
            parent::setComponent($responseProcessing);
        } else {
            $msg = 'The ResponseProcessingEngine class only accepts ResponseProcessing objects to be executed.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Add a template mapping.
     *
     * @param string $uri The template URI (Uniform Resource Identifier).
     * @param string $url The actual template URL, i.e. where to find the file containing the template markup.
     * @throws InvalidArgumentException If $uri or $url are not strings.
     */
    public function addTemplateMapping($uri, $url)
    {
        if (!is_string($uri)) {
            $msg = "The uri argument must be a string, '" . gettype($uri) . "' given.";
            throw new InvalidArgumentException($msg);
        }

        if (!is_string($url)) {
            $msg = "The url argument must be a string, '" . gettype($uri) . "' given.";
            throw new InvalidArgumentException($msg);
        }

        $templateMapping = &$this->getTemplateMapping();
        $templateMapping[$uri] = $url;
    }

    /**
     * Remove a template mapping for a given $uri. If no template mapping
     * is found for $uri, nothing happens.
     *
     * @param string $uri The $uri you want to remove the mapping.
     * @throws InvalidArgumentException If $uri is not a string.
     */
    public function removeTemplateMapping($uri)
    {
        if (!is_string($uri)) {
            $msg = "The uri argument must be a string, '" . gettype($uri) . "' given.";
            throw new InvalidArgumentException($msg);
        }

        $templateMapping = &$this->getTemplateMapping();

        if (isset($templateMapping[$uri])) {
            unset($templateMapping[$uri]);
        }
    }

    /**
     * Get the current template mapping array.
     *
     * @return array An array where keys are template URIs and values template URL (their location).
     */
    protected function &getTemplateMapping()
    {
        return $this->templateMapping;
    }

    /**
     * Execute the ResponseProcessing according to the current context.
     *
     * The following sub-types of ProcessingException may be thrown:
     *
     * * RuleProcessingException: If a ResponseRule in the ResponseProcessing produces an error OR if the ExitResponse rule is invoked. In this last case, a specific exception code will be produced to deal with the situation accordingly.
     * * ExpressionProcessingException: If an Expression within a ResponseRule produces an error.
     * * ResponseProcessingException: If there is a problem with the response processing template processing bound to the ResponseProcessing.
     *
     * @throws ProcessingException
     * @throws PhpStorageException
     */
    public function process()
    {
        $rules = $this->getResponseProcessingRules();
        $processingCollectionException = null;

        foreach ($rules as $rule) {
            try {
                $engine = new RuleEngine($rule, $this->getContext());
                $engine->process();
                $this->trace($rule->getQtiClassName() . ' executed');
            } catch (ProcessingException $exception) {
                if ($processingCollectionException === null) {
                    $processingCollectionException = new ProcessingCollectionException('Unexpected error(s) occurred while processing response');
                }

                $processingCollectionException->addProcessingExceptions($exception);
            }
        }

        if ($processingCollectionException !== null) {
            throw $processingCollectionException;
        }
    }

    /**
     * @return mixed
     * @throws PhpStorageException
     */
    public function getResponseProcessingRules()
    {
        // @todo Figure out how to provide a way to the ResponseProcessingEngine to know the folder where to seek for templateLocation, which is a relative URI.
        $responseProcessing = $this->getComponent();
        $template = $responseProcessing->getTemplate();
        $templateLocation = $responseProcessing->getTemplateLocation();

        if (count($responseProcessing->getResponseRules()) > 0) {
            // Always prefer the embedded rules.
            $rules = $responseProcessing->getResponseRules();
        } else {
            $finalTemplateFile = '';

            if (empty($template) === false) {
                // try to locate the template file thanks to the given mapping.
                $mapping = $this->getTemplateMapping();
                if (isset($mapping[$template])) {
                    $finalTemplateFile = $mapping[$template];
                }
            }

            // The template could not be resolved using the mapping.
            // Try to use template location.
            if (empty($finalTemplateFile) && empty($templateLocation) === false && @is_readable($templateLocation)) {
                $finalTemplateFile = $templateLocation;
            }

            if (empty($finalTemplateFile)) {
                $msg = "The template file could not be found: template='${template}', templateLocation='${templateLocation}'.";
                throw new ResponseProcessingException($msg, $this, ResponseProcessingException::TEMPLATE_NOT_FOUND);
            }

            // Open the file and retrieve the rules.
            $this->trace("loading response processing template '${finalTemplateFile}'");
            $php = new PhpDocument();
            $php->load($finalTemplateFile);
            $rules = $php->getDocumentComponent()->getResponseRules();
            $this->trace(count($rules) . ' responseRule(s) extracted from the response processing template');
        }

        return $rules;
    }
}
