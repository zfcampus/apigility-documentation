<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

$modules = array(
    'zf-apigility',
    'zf-apigility-admin',
    'zf-apigility-documentation',
    'zf-apigility-documentation-apiblueprint',
    'zf-apigility-documentation-swagger',
    'zf-apigility-doctrine',
    'zf-apigility-provider',
    'zf-apigility-welcome',
    'zf-api-problem',
    'zf-configuration',
    'zf-console',
    'zf-content-negotiation',
    'zf-content-validation',
    'zf-deploy',
    'zf-development-mode',
    'zf-doctrine-querybuilder',
    'zf-hal',
    'zf-http-cache',
    'zf-mvc-auth',
    'zf-oauth2',
    'zf-rest',
    'zf-rpc',
    'zf-versioning',
);

$uriTemplate  = 'https://raw.githubusercontent.com/zfcampus/%s/master/README.md';
$pathTemplate = realpath(__DIR__) . '/../modules/%s.md';
$regexReplace = array(
    array('pattern' => '#\n\[\!\[build status\].*?\n#is',    'replacement' => ''),
    array('pattern' => '#\n\[\!\[coverage status\].*?\n#is', 'replacement' => ''),
    array('pattern' => '#\[(.*)\]\(((?![http|\#]).+)\)#is', 'replacement' => '[$1](https://github.com/zfcampus/%s/tree/master/$2)')
);

// Set up multicall
$multiCall   = curl_multi_init();
$handles     = array();

// Add handles for all modules
foreach ($modules as $module) {
    $uri              = sprintf($uriTemplate, $module);
    $handles[$module] = curl_init($uri);
    curl_setopt($handles[$module], CURLOPT_HEADER, 0);
    curl_setopt($handles[$module], CURLOPT_RETURNTRANSFER, 1);

    curl_multi_add_handle($multiCall, $handles[$module]);
}

// Execute all handles
do {
    $result = curl_multi_exec($multiCall, $running);
} while ($running > 0);

// Get content and close handles
$results = array();
foreach ($handles as $module => $handle) {
    $results[$module] = curl_multi_getcontent($handle);
    curl_multi_remove_handle($multiCall, $handle);
    curl_close($handle);
    unset($handles[$module]);
}
curl_multi_close($multiCall);

// Pre-process markdown and write file to repository
foreach ($results as $module => $markdown) {
    foreach ($regexReplace as $info) {
        $info['replacement'] = sprintf($info['replacement'], $module);
        $markdown = preg_replace($info['pattern'], $info['replacement'], $markdown);
    }

    $path = sprintf($pathTemplate, $module);
    file_put_contents($path, $markdown);
}
