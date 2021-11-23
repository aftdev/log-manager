<?php

namespace AftDevTest\Log;

use AftDev\Log\ConfigProvider;
use AftDev\Test\TestCase;

/**
 * @internal
 * @covers \AftDev\Log\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    /**
     * Test config provider.
     */
    public function testConfigProvider()
    {
        $provider = new ConfigProvider();

        $config = $provider();

        $this->assertArrayHasKey('log', $config);
    }
}
