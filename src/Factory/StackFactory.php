<?php

namespace AftDev\Log\Factory;

use AftDev\Log\LoggerManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

class StackFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var LoggerManager $logManager */
        $logManager = $container->get(LoggerManager::class);

        $channels = $options['channels'] ?? [];
        if (empty($channels)) {
            throw new ServiceNotCreatedException('Channels options is missing.');
        }

        $handlers = [];
        foreach ($channels as $channel) {
            $handlers = array_merge($handlers, $logManager->channel($channel)->getHandlers());
        }

        return new Logger($requestedName, $handlers);
    }
}
