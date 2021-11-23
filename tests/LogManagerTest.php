<?php

namespace AftDevTest\Log;

use AftDev\Log\LoggerManager;
use AftDev\Test\TestCase;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @covers \AftDev\Log\LoggerManager
 */
class LogManagerTest extends TestCase
{
    public function testChannels()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $managerConfig = [
            'factories' => [
                'test' => function () {
                    $handler = $this->prophesize(HandlerInterface::class);
                    $logger = $this->prophesize(Logger::class);

                    $logger->getHandlers()->willReturn([$handler->reveal()]);

                    return $logger->reveal();
                },
            ],
            'shared_by_default' => false,

            'plugins' => [
                'stack' => [
                    'options' => [
                        'channels' => ['test_channel', 'test_channel_2'],
                    ],
                ],
                'test_channel' => [
                    'service' => 'test',
                    'options' => ['a' => 'a'],
                ],
                'test_channel_2' => [
                    'service' => 'test',
                    'options' => ['b' => 'b'],
                ],
            ],
        ];

        $manager = new LoggerManager($container->reveal(), $managerConfig);

        $container->get(LoggerManager::class)->willReturn($manager);

        /** @var Logger $logger */
        $logger = $manager->channel('test_channel');

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertCount(1, $logger->getHandlers());

        $stack = $manager->stack('test_channel', 'test_channel_2');

        $this->assertInstanceOf(Logger::class, $stack);
        $this->assertCount(2, $stack->getHandlers());

        $sameStack = $manager->stack('test_channel', 'test_channel_2');
        $this->assertSame($stack, $sameStack);

        $stackArrayNotation = $manager->stack(['test_channel_2', 'test_channel']);

        $this->assertInstanceOf(Logger::class, $stackArrayNotation);
        $this->assertCount(2, $stackArrayNotation->getHandlers());
    }

    /**
     * Test that LogManager magic function redirect to the default logger.
     */
    public function testMagicFunctions()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $loggerDefaultStub = $this->prophesize(LoggerInterface::class);
        $loggerStubTwo = $this->prophesize(LoggerInterface::class);

        $managerConfig = [
            'factories' => [
                'test' => function () use ($loggerDefaultStub) {
                    return $loggerDefaultStub->reveal();
                },
                'test2' => function () use ($loggerStubTwo) {
                    return $loggerStubTwo->reveal();
                },
            ],
            'shared_by_default' => false,
            'default' => 'test',
            'plugins' => [
                'test' => [
                    'plugin' => 'test',
                ],
                'test2' => [
                    'plugin' => 'test',
                ],
            ],
        ];

        $manager = new LoggerManager($container->reveal(), $managerConfig);

        $methods = [
            'debug',
            'alert',
            'emergency',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
        ];

        $message = 'test';
        $context = ['context' => 'a'];

        foreach ($methods as $method) {
            $loggerDefaultStub->{$method}($message, $context)->shouldBeCalledTimes(1);
            $loggerStubTwo->{$method}(Argument::any())->shouldNotBeCalled();
        }

        foreach ($methods as $method) {
            $manager->{$method}($message, $context);
        }
    }

    /**
     * Test the an exception is thrown when using invalid function.
     */
    public function testInvalidMagicFunction()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $manager = new LoggerManager($container->reveal(), []);

        $this->expectException(\BadMethodCallException::class);
        $manager->invalidLogFunction('test');
    }
}
