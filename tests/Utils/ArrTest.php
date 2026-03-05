<?php

declare(strict_types=1);

namespace LPhenom\Core\Tests\Utils;

use LPhenom\Core\Utils\Arr;
use PHPUnit\Framework\TestCase;

final class ArrTest extends TestCase
{
    public function testGetDotTopLevel(): void
    {
        $arr = ['key' => 'value'];
        self::assertSame('value', Arr::getDot($arr, 'key'));
    }

    public function testGetDotNested(): void
    {
        $arr = ['a' => ['b' => ['c' => 42]]];
        self::assertSame(42, Arr::getDot($arr, 'a.b.c'));
    }

    public function testGetDotReturnsDefaultOnMissing(): void
    {
        $arr = ['a' => ['b' => 1]];
        self::assertSame('default', Arr::getDot($arr, 'a.x', 'default'));
        self::assertNull(Arr::getDot($arr, 'missing'));
    }

    public function testGetDotReturnsDefaultWhenIntermediaryNotArray(): void
    {
        $arr = ['a' => 'not_an_array'];
        self::assertSame('fallback', Arr::getDot($arr, 'a.b', 'fallback'));
    }

    public function testSetDotTopLevel(): void
    {
        $arr = [];
        Arr::setDot($arr, 'key', 'value');
        self::assertSame('value', $arr['key']);
    }

    public function testSetDotNested(): void
    {
        $arr = [];
        Arr::setDot($arr, 'a.b.c', 99);
        self::assertSame(99, $arr['a']['b']['c']);
    }

    public function testSetDotOverwritesExisting(): void
    {
        $arr = ['a' => ['b' => 1]];
        Arr::setDot($arr, 'a.b', 2);
        self::assertSame(2, $arr['a']['b']);
    }

    public function testSetDotCreatesIntermediateArrays(): void
    {
        $arr = [];
        Arr::setDot($arr, 'x.y.z', 'hello');
        self::assertSame('hello', $arr['x']['y']['z']);
    }

    public function testGetDotReturnsFalseCorrectly(): void
    {
        $arr = ['flag' => false];
        self::assertFalse(Arr::getDot($arr, 'flag'));
    }

    public function testGetDotReturnsNullCorrectly(): void
    {
        $arr = ['val' => null];
        self::assertNull(Arr::getDot($arr, 'val', 'default'));
    }
}
