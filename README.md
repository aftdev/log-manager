# Log Manager.

Log manager for [monolog](https://github.com/Seldaek/monolog/)

## Usage

```php
class X {
    __construct(LogManager $logManager) {
        // Use default channel.
        $logManager->warning('Default channel warning');

        // Or
        $logManager->channel()->warning('Default channel warning');

        // Specify what channel to use.
        $logManager->channel('daily')->warning('Daily Warning');
    }
}
```

### Default Logger

By default the `Psr\Logger\Interface` is linked to the default handler from the
configuration

You can easily use it in your classes.

```php
__construct(\Psr\Log\LoggerInterface $logger) {
    $logger->debug('Hello from default logger');
}
```

## Config

Default configurations include:

- `daily` -> rotating file logger in the /data/log folder. (RotatingFileHandler)
- `syslog` -> will use the syslog. (SyslogHandler)
- `stream` -> to use a stream. (StreamHandler)

All handlers provided by monolog are usable by the log manager

```php
use Monolog\Level;

return [
    'log' => [
         'default' => 'stack',
         'plugins' => [
             'stack' => [
                 'plugin' => 'stack',
                 'options' => [
                     'channels' => ['daily'],
                 ],
             ],
             'daily' => [
                 'plugin' => 'daily',
                 'options' => [
                     'level' => Level::Debug,
                     'filename' => 'data/log/application.log',
                     'maxFiles' => 5,
                 ],
             ],
             'filesystem' => [
                 'plugin' => 'stream',
                 'options' => [
                     'filename' => 'path_to_file',
                     'level' => Level::Debug,
                 ],
             ],
         ],
    ];
]
```

## Monolog Handlers

Any monolog
[handlers](https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#handlers)
are available to be used via the `'plugin'` option. The `options` values will be
used as arguments when creating the handler.

```php
return [
    'log' [
         'default' => 'stack',
         'plugins' => [
             'monolog_handler' => [
                 'plugin' => SlackHandler::class,
                 'options' => [
                     'token' => 'XXX',
                 ],
             ],
         ],
    ];
]
```

If you require more customization you can create your own handler factories.

```php
return [
    'log_manager' => [
        'factories' => [
            'amazon' => function ($container) {
                $sqsClient = $container->get('SqSClient');
                return new SqsHandler($sqsClient);
            }
        ],
    ],
    'log' [
         'default' => 'stack',
         'plugins' => [
             'monolog_handler' => [
                 'plugin' => 'amazon',
             ],
         ],
    ];
]
```

## Stack

To create a logger that will use several handlers, use the `stack` plugin.

```php
return [
    'log' => [
         'default' => 'daily',
         'plugins' => [
            'stack' => [
                'plugin' => 'stack',
                'options' => [
                    'channels' => ['handler_1', 'handler_2'],
                ],
            ],
            'handler_1' => [],
            'handler_2' => [],
         ],
    ],
];
```

To create a new stack on the fly you can use the LogManager.

```php
$logManager->stack('handler_1', 'handler_2')->warning('Warning');
$logManager->stack(['handler_1', 'handler_2'])->warning('Warning - Array notations');
```

## Formatters

By default each handler will use the default LineFormatter formatter with
default options. You can easily customize it by using the the `formatter`
option.

```php
return [
    'log' => [
         'default' => 'daily',
         'plugins' => [
            'with_formatter' => [
                'plugin' => 'daily',
                'options' => [
                    'formatter' => 'HtmlFormatter::class',
                ],
            ],
            'with_formatter' => [
                'plugin' => 'daily',
                'options' => [
                    'formatter' => [
                         LineFormatter::class,
                         [
                            'format' => '[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n']
                         ],
                ],
            ],
         ],
    ],
];
```

The formatter will be fetched from the Service Manager so if you require more
customization uses that feature.

```php
return [
    'dependencies' => [
         'factories' => [
              'MyCustomFormatter' => FormatterFactory::class,
          ]
    ],
    'log' => [
         'plugins' => [
            'custom_formatter' => [
                'plugin' => 'daily',
                'options' => [
                    'formatter' => 'MyCustomFormatter',
                ],
            ],
         ],
    ],
];
```

## Processors

To add extra context to your log messages automatically you can use the
`processors` options.

Monolog comes out of the box with a bunch of
[processors](https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md#processor)

Similarly to the formatter, the service manager is used to build the processors
instances.

```php
return [
    'dependencies' => [
          'factories' => [
              'MyCustomProcessor' => ProcessorFactory::class,
          ]
    ],
    'log' => [
         'plugins' => [
            'custom_formatter' => [
                'plugin' => 'daily',
                'options' => [
                    'processors' => [
                         Monolog\Processor\MemoryUsage::class,
                         'MyCustomProcessor',
                    ]
                ],
            ],
         ],
    ],
];
```
