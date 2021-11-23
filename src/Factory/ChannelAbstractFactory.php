<?php

namespace AftDev\Log\Factory;

use AftDev\ServiceManager\Factory\ReflectionAbstractFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

class ChannelAbstractFactory extends ReflectionAbstractFactory
{
    public function __invoke(ContainerInterface $container, $handlerName, array $options = null)
    {
        $handler = parent::__invoke($container, $handlerName, $options ?? []);

        $formatter = $this->getFormatter($container, $options ?? []);
        $handler->setFormatter($formatter);

        return new Logger($handlerName, [$handler]);
    }

    /**
     * Return formatter based on options.
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    protected function getFormatter(ContainerInterface $container, array $options): FormatterInterface
    {
        $formatterOptions = (array) ($options['formatter'] ?? []);

        $formatterName = $formatterOptions[0] ?? LineFormatter::class;
        $formatterOptions = $formatterOptions[1] ?? [];

        if ($container->has($formatterName)) {
            $formatter = $container->get($formatterName, $formatterOptions);
        } else {
            $formatter = parent::__invoke($container, $formatterName, $formatterOptions ?? []);
        }

        return $formatter;
    }
}
