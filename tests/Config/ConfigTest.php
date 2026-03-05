<?php

declare(strict_types=1);

namespace LPhenom\Core\Tests\Config;

use LPhenom\Core\Config\Config;
use LPhenom\Core\Config\ConfigException;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config([
            'app' => [
                'name'  => 'LPhenom',
                'debug' => true,
                'port'  => 8080,
                'tags'  => ['php', 'kphp'],
            ],
            'db' => [
                'host' => 'localhost',
                'port' => 3306,
            ],
            'flat_key' => 'flat_value',
        ]);
    }

    public function testGetReturnsValue(): void
    {
        self::assertSame('flat_value', $this->config->get('flat_key'));
    }

    public function testGetReturnsDefaultWhenKeyMissing(): void
    {
        self::assertSame('default', $this->config->get('missing_key', 'default'));
        self::assertNull($this->config->get('missing_key'));
    }

    public function testGetStringReturnsString(): void
    {
        self::assertSame('LPhenom', $this->config->getString('app.name'));
    }

    public function testGetStringThrowsOnNonString(): void
    {
        $this->expectException(ConfigException::class);
        $this->config->getString('app.port');
    }

    public function testGetStringReturnsDefault(): void
    {
        self::assertSame('fallback', $this->config->getString('missing', 'fallback'));
    }

    public function testGetIntReturnsInt(): void
    {
        self::assertSame(8080, $this->config->getInt('app.port'));
    }

    public function testGetIntThrowsOnNonInt(): void
    {
        $this->expectException(ConfigException::class);
        $this->config->getInt('app.name');
    }

    public function testGetIntReturnsDefault(): void
    {
        self::assertSame(42, $this->config->getInt('missing', 42));
    }

    public function testGetBoolReturnsBool(): void
    {
        self::assertTrue($this->config->getBool('app.debug'));
    }

    public function testGetBoolThrowsOnNonBool(): void
    {
        $this->expectException(ConfigException::class);
        $this->config->getBool('app.name');
    }

    public function testGetBoolReturnsDefault(): void
    {
        self::assertFalse($this->config->getBool('missing', false));
    }

    public function testGetArrayReturnsArray(): void
    {
        self::assertSame(['php', 'kphp'], $this->config->getArray('app.tags'));
    }

    public function testGetArrayThrowsOnNonArray(): void
    {
        $this->expectException(ConfigException::class);
        $this->config->getArray('app.name');
    }

    public function testGetArrayReturnsDefault(): void
    {
        self::assertSame(['a', 'b'], $this->config->getArray('missing', ['a', 'b']));
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        self::assertTrue($this->config->has('app.name'));
        self::assertTrue($this->config->has('flat_key'));
        self::assertTrue($this->config->has('db'));
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        self::assertFalse($this->config->has('missing'));
        self::assertFalse($this->config->has('app.missing_nested'));
        self::assertFalse($this->config->has('app.name.extra'));
    }

    public function testDotNotationDeepNesting(): void
    {
        self::assertSame('localhost', $this->config->getString('db.host'));
        self::assertSame(3306, $this->config->getInt('db.port'));
    }

    public function testGetStringThrowsWhenKeyMissingAndNoDefault(): void
    {
        $this->expectException(ConfigException::class);
        $this->config->getString('totally_missing_key');
    }
}
