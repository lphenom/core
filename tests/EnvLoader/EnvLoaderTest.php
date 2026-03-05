<?php

declare(strict_types=1);

namespace LPhenom\Core\Tests\EnvLoader;

use LPhenom\Core\EnvLoader\EnvLoader;
use PHPUnit\Framework\TestCase;

final class EnvLoaderTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'lphenom_env_test_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    private function writeEnv(string $content): void
    {
        file_put_contents($this->tmpFile, $content);
    }

    public function testLoadsSimpleKeyValue(): void
    {
        $this->writeEnv("APP_NAME=LPhenom\nAPP_ENV=production\n");

        $loader = new EnvLoader();
        $loader->load($this->tmpFile);

        self::assertSame('LPhenom', $loader->get('APP_NAME'));
        self::assertSame('production', $loader->get('APP_ENV'));
    }

    public function testSkipsCommentLines(): void
    {
        $this->writeEnv("# This is a comment\nAPP_KEY=secret\n");

        $loader = new EnvLoader();
        $loader->load($this->tmpFile);

        self::assertSame('secret', $loader->get('APP_KEY'));
    }

    public function testSkipsEmptyLines(): void
    {
        $this->writeEnv("\n\nAPP_VER=1.0\n\n");

        $loader = new EnvLoader();
        $loader->load($this->tmpFile);

        self::assertSame('1.0', $loader->get('APP_VER'));
    }

    public function testStripsDoubleQuotes(): void
    {
        $this->writeEnv('DB_PASS="my secret pass"' . "\n");

        $loader = new EnvLoader();
        $loader->load($this->tmpFile);

        self::assertSame('my secret pass', $loader->get('DB_PASS'));
    }

    public function testStripsSingleQuotes(): void
    {
        $this->writeEnv("DB_HOST='localhost'\n");

        $loader = new EnvLoader();
        $loader->load($this->tmpFile);

        self::assertSame('localhost', $loader->get('DB_HOST'));
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        $loader = new EnvLoader();
        self::assertSame('fallback', $loader->get('TOTALLY_MISSING_KEY', 'fallback'));
        self::assertNull($loader->get('TOTALLY_MISSING_KEY'));
    }

    public function testValueWithEqualsSign(): void
    {
        // Value itself contains '=' — only the first '=' is the separator
        $this->writeEnv("DSN=mysql://user:pass@host/db?param=1\n");

        $loader = new EnvLoader();
        $loader->load($this->tmpFile);

        self::assertSame('mysql://user:pass@host/db?param=1', $loader->get('DSN'));
    }

    public function testThrowsOnMissingFile(): void
    {
        $loader = new EnvLoader();

        $this->expectException(\RuntimeException::class);
        $loader->load('/non/existent/path/.env');
    }
}
