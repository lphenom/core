<?php

declare(strict_types=1);

namespace LPhenom\Core\Tests\Utils;

use LPhenom\Core\Utils\Str;
use PHPUnit\Framework\TestCase;

final class StrTest extends TestCase
{
    public function testStartsWithTrue(): void
    {
        self::assertTrue(Str::startsWith('LPhenom Framework', 'LPhenom'));
        self::assertTrue(Str::startsWith('hello', 'hello'));
        self::assertTrue(Str::startsWith('hello', ''));
    }

    public function testStartsWithFalse(): void
    {
        self::assertFalse(Str::startsWith('LPhenom', 'KPHP'));
        self::assertFalse(Str::startsWith('hello', 'world'));
    }

    public function testEndsWithTrue(): void
    {
        self::assertTrue(Str::endsWith('LPhenom Framework', 'Framework'));
        self::assertTrue(Str::endsWith('hello', 'hello'));
        self::assertTrue(Str::endsWith('hello', ''));
    }

    public function testEndsWithFalse(): void
    {
        self::assertFalse(Str::endsWith('LPhenom', 'KPHP'));
        self::assertFalse(Str::endsWith('hello', 'world'));
    }

    public function testStartsWithEmptyNeedle(): void
    {
        self::assertTrue(Str::startsWith('anything', ''));
    }

    public function testEndsWithEmptyNeedle(): void
    {
        self::assertTrue(Str::endsWith('anything', ''));
    }

    public function testStartsWithEmptyHaystack(): void
    {
        self::assertTrue(Str::startsWith('', ''));
        self::assertFalse(Str::startsWith('', 'a'));
    }

    public function testContainsTrue(): void
    {
        self::assertTrue(Str::contains('LPhenom Framework', 'Framework'));
        self::assertTrue(Str::contains('LPhenom Framework', 'LPhenom'));
        self::assertTrue(Str::contains('hello', 'ell'));
        self::assertTrue(Str::contains('hello', ''));
    }

    public function testContainsFalse(): void
    {
        self::assertFalse(Str::contains('hello', 'world'));
        self::assertFalse(Str::contains('LPhenom', 'KPHP'));
    }

    public function testContainsEmptyHaystack(): void
    {
        self::assertTrue(Str::contains('', ''));
        self::assertFalse(Str::contains('', 'a'));
    }
}
