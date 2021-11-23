<?php

namespace AftDev\Log\Factory;

use AftDev\Log\LoggerManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DefaultLoggerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $handlerName, array $options = null)
    {
        $logManager = $container->get(LoggerManager::class);

        return $logManager->getDefault();
    }
}
