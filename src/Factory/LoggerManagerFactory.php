<?php

namespace AftDev\Log\Factory;

use AftDev\Log\LoggerManager;
use AftDev\ServiceManager\Factory\AbstractManagerFactory;

class LoggerManagerFactory extends AbstractManagerFactory
{
    protected $managerClass = LoggerManager::class;

    protected $configKey = 'log';
}
