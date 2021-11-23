<?php

namespace AftDevTest\Log;

use AftDev\Log\Factory\StackFactory;
use AftDev\Log\LoggerManager;
use AftDev\Test\TestCase;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @covers \AftDev\Log\Factory\StackFactory
 */
class StackFactoryTest extends TestCase
{
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $logManager = $this->prophesize(LoggerManager::class);
        $logger = $this->prophesize(Logger::class);
        $handler = $this->prophesize(HandlerInterface::class);

        $container->get(LoggerManager::class)->willReturn($logManager->reveal())->shouldBeCalled(1);
        $logManager->channel(Argument::any())->willReturn($logger->reveal())->shouldBeCalledTimes(3);
        $logger->getHandlers(Argument::any())->willReturn([$handler->reveal()])->shouldBeCalledTimes(3);

        $factory = new StackFactory();
        $stackedLogger = $factory($container->reveal(), 'stack', [
            'channels' => ['a', 'b', 'c'],
        ]);

        $this->assertCount(3, $stackedLogger->getHandlers());
    }

    public function testFactoryWithBadOptions()
    {
        $this->expectException(ServiceNotCreatedException::class);

        $container = $this->prophesize(ContainerInterface::class);

        $factory = new StackFactory();
        $factory($container->reveal(), 'stack', []);
    }
}
