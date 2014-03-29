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
        return array(
            'asset_manager' => array(
                'resolver_configs' => array(
                    'paths' => array(
                        __DIR__ . '/asset',
                    ),
                ),
            ),
            'apigility-documentation' => array(
                'path' => __DIR__,
            ),
        );
    }
}
