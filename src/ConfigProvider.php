<?php

namespace AftDev\Log;

use AftDev\Log\Factory\ChannelAbstractFactory;
use Monolog\Level;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public const CONFIG_KEY = 'log';

    public function __invoke()
    {
        $config['dependencies'] = $this->getDependencyConfig();
        $config[self::CONFIG_KEY] = $this->getLogManagerConfig();

        return $config;
    }

    public function getDependencyConfig()
    {
        return [
            'factories' => [
                LoggerManager::class => Factory\LoggerManagerFactory::class,
                LoggerInterface::class => Factory\DefaultLoggerFactory::class,
            ],
        ];
    }

    public function getLogManagerConfig()
    {
        return [
            'default' => 'stack',
            'plugins' => [
                'stack' => [
                    'channels' => ['daily'],
                ],
                'daily' => [
                    'level' => Level::Debug,
                    'filename' => 'data/log/daily.log',
                    'maxFiles' => 5,
                ],
                'filesystem' => [
                    'plugin' => 'stream',
                    'options' => [
                        'filename' => 'data/log/application.log',
                        'level' => Level::Debug,
                    ],
                ],
            ],
            'abstract_factories' => [
                'default' => ChannelAbstractFactory::class,
            ],
        ];
    }
}
