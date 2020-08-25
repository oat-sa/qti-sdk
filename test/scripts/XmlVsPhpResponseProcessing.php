<?php

use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\xml\XmlDocument;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

/**
 * @param $start
 * @param $end
 * @return mixed
 */
function spentTime($start, $end)
{
    $startTime = explode(' ', $start);
    $endTime = explode(' ', $end);
    return ($endTime[0] + $endTime[1]) - ($startTime[0] + $startTime[1]);
}

$basePath = '/../../src/qtism/runtime/processing/templates/2_1/';
$templates = ['match_correct', 'map_response', 'map_response_point'];

$xmlTimings = ['match_correct' => [], 'map_response' => [], 'map_response_point' => []];
$phpTimings = ['match_correct' => [], 'map_response' => [], 'map_response_point' => []];
$iterations = 100;

foreach ($templates as $t) {
    for ($i = 0; $i < $iterations; $i++) {
        // --- XML
        $start = microtime();

        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load(dirname(__FILE__) . $basePath . $t . '.xml');

        $end = microtime();
        $xmlTimings[$t][] = spentTime($start, $end);

        // --- PHP
        $start = microtime();

        $phpDoc = new PhpDocument();
        $phpDoc->load(dirname(__FILE__) . $basePath . $t . '.php');

        $end = microtime();
        $phpTimings[$t][] = spentTime($start, $end);
    }
}

foreach ($templates as $t) {
    // compute arithmetic mean.
    $meanXml = 0;
    foreach ($xmlTimings[$t] as $v) {
        $meanXml += $v;
    }
    $meanXml = $meanXml / $iterations;

    // compute arithmetic mean.
    $meanPhp = 0;
    foreach ($phpTimings[$t] as $v) {
        $meanPhp += $v;
    }
    $meanPhp = $meanPhp / $iterations;

    echo "+ ${t} (XML = ${meanXml} - PHP ${meanPhp})\n";
}
