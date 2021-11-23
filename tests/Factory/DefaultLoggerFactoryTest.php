<?php

namespace AftDevTest\Log\Factory;

use AftDev\Log\Factory\DefaultLoggerFactory;
use AftDev\Log\LoggerManager;
use AftDev\Test\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class DefaultLoggerFactoryTest extends TestCase
{
    /**
     * Test that factory return default value.
     */
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $logManager = $this->prophesize(LoggerManager::class);

        $container->get(Argument::any())->willReturn($logManager->reveal());
        $logManager->getDefault()->willReturn(true);

        $factory = new DefaultLoggerFactory();
        $defaultLogger = $factory($container->reveal(), 'test');

        $this->assertTrue($defaultLogger);
    }
}
