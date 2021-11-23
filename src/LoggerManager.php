<?php

namespace AftDev\Log;

use AftDev\Log\Factory\StackFactory;
use AftDev\ServiceManager\AbstractManager;
use Monolog\Handler;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerManager.
 *
 * @method debug($message, array $context = array())
 * @method alert($message, array $context = array())
 * @method emergency($message, array $context = array())
 * @method critical($message, array $context = array())
 * @method error($message, array $context = array())
 * @method warning($message, array $context = array())
 * @method notice($message, array $context = array())
 * @method info($message, array $context = array())
 */
class LoggerManager extends AbstractManager
{
    protected $instanceOf = LoggerInterface::class;

    protected $factories = [
        'stack' => StackFactory::class,
    ];

    protected $aliases = [
        'stream' => Handler\StreamHandler::class,
        'daily' => Handler\RotatingFileHandler::class,
        'syslog' => Handler\SyslogHandler::class,
        'error' => Handler\ErrorLogHandler::class,
    ];

    /**
     * List of channels.
     *
     * @var array
     */
    protected $stacks = [];

    /**
     * Shortcuts to the default logger functions.
     *
     * @param $name
     * @param $args
     *
     * @return false|LoggerInterface
     */
    public function __call($name, $args)
    {
        $functions = [
            'debug',
            'alert',
            'emergency',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
        ];

        if (!in_array($name, $functions)) {
            throw new \BadMethodCallException('Invalid log function ['.$name.']');
        }

        return $this->channel()->{$name}(...$args);
    }

    /**
     * Get logger for given channel.
     *
     * @param null|string $channel - Channel name to use or leave empty to use default one.
     */
    public function channel(string $channel = null): LoggerInterface
    {
        if (null === $channel) {
            return $this->getDefault();
        }

        return $this->getPlugin($channel);
    }

    /**
     * Return logger for given stack of channels.
     *
     * @param array $channels
     */
    public function stack(...$channels): LoggerInterface
    {
        $channels = is_array($channels[0]) ? $channels[0] : $channels;
        $channel = $this->createStack($channels);

        return $this->channel($channel);
    }

    /**
     * Create a stacked channel.
     *
     * @return string - The stack name.
     */
    protected function createStack(array $channelNames)
    {
        $stackName = 'stack_'.md5(implode('-', $channelNames));
        if (isset($this->pluginsOptions[$stackName])) {
            return $stackName;
        }

        $this->pluginsOptions[$stackName] = [
            'service' => 'stack',
            'options' => [
                'channels' => $channelNames,
            ],
        ];

        return $stackName;
    }
}
