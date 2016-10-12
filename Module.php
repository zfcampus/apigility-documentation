<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ApigilityDocumentation;

class Module
{
    public function getConfig()
    {
        return [
            'asset_manager' => [
                'resolver_configs' => [
                    'paths' => [
                        __DIR__ . '/asset',
                    ],
                ],
            ],
            'apigility-documentation' => [
                'path' => realpath(__DIR__),
            ],
        ];
    }
}
