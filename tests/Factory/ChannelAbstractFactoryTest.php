<?php

namespace AftDevTest\Log\Factory;

use AftDev\Log\Factory\ChannelAbstractFactory;
use AftDev\ServiceManager\Resolver;
use AftDev\Test\TestCase;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessorInterface;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @covers \AftDev\Log\Factory\ChannelAbstractFactory
 */
class ChannelAbstractFactoryTest extends TestCase
{
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $factory = new ChannelAbstractFactory();

        /** @var Logger $handler */
        $logger = $factory($container->reveal(), RotatingFileHandler::class, [
            'level' => 'debug',
            'filename' => 'test name',
        ]);

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testFormatter()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $factory = new ChannelAbstractFactory();

        /** @var Logger $handler */
        $logger = $factory($container->reveal(), RotatingFileHandler::class, [
            'level' => 'debug',
            'filename' => 'test name',
        ]);

        $handler = current($logger->getHandlers());

        $this->assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $logger = $factory($container->reveal(), RotatingFileHandler::class, [
            'level' => 'debug',
            'filename' => 'test name',
            'formatter' => [HtmlFormatter::class, [
                'dateFormat' => 'CUSTOM FORMAT',
            ]],
        ]);

        $handler = current($logger->getHandlers());

        $this->assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $formatter = $handler->getFormatter();
        $reflection = new \ReflectionClass($formatter);

        $reflection = $reflection->getProperty('dateFormat');
        $reflection->setAccessible(true);

        $this->assertSame('CUSTOM FORMAT', $reflection->getValue($formatter));
    }

    public function testFormatterFromContainer()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Resolver::class)->willReturn(false);
        $formatter = $this->prophesize(FormatterInterface::class);

        $container->has('FancyFormatter')->willReturn(true);
        $container->get('FancyFormatter', Argument::cetera())
            ->willReturn($formatter->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $factory = new ChannelAbstractFactory();
        $logger = $factory($container->reveal(), RotatingFileHandler::class, [
            'level' => 'debug',
            'filename' => 'test name',
            'formatter' => 'FancyFormatter',
        ]);

        $handler = current($logger->getHandlers());
        $this->assertSame($formatter->reveal(), $handler->getFormatter());
    }

    public function testEmptyProcessors()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $factory = new ChannelAbstractFactory();

        /** @var Logger $handler */
        $logger = $factory($container->reveal(), RotatingFileHandler::class, [
            'level' => 'debug',
            'filename' => 'test name',
        ]);

        $handler = current($logger->getHandlers());
        $this->expectException(\LogicException::class);

        $handler->popProcessor();
    }

    public function testProcessors()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Resolver::class)->willReturn(false);
        $container->has(\Monolog\Formatter\LineFormatter::class)->willReturn(false);
        $processor = $this->prophesize(ProcessorInterface::class);

        $container->has(MemoryUsageProcessor::class)->willReturn(false);
        $container->has('FancyProcessor')->willReturn(true);
        $container->get('FancyProcessor', Argument::cetera())
            ->willReturn($processor->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $factory = new ChannelAbstractFactory();

        /** @var Logger $handler */
        $logger = $factory($container->reveal(), RotatingFileHandler::class, [
            'level' => 'debug',
            'filename' => 'test name',
            'processors' => [
                MemoryUsageProcessor::class,
                'FancyProcessor',
            ],
        ]);

        $handler = current($logger->getHandlers());

        $processors = [];

        try {
            while ($proc = $handler->popProcessor()) {
                $processors[] = $proc;
            }
        } catch (\LogicException $e) {
        }

        $this->assertCount(2, $processors);
        $this->assertSame($processor->reveal(), $processors[0]);
        $this->assertInstanceOf(MemoryUsageProcessor::class, $processors[1]);
    }
}
