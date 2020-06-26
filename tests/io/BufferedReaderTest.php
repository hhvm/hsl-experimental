<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\{IO, OS};

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class BufferedReaderTest extends HackTest {
  public async function testReadByte(): Awaitable<void> {
    $r = new IO\BufferedReader(new IO\MemoryHandle('abc'));
    $a = await $r->readByteAsync();
    $b = await $r->readByteAsync();
    $c = await $r->readByteAsync();
    expect(vec[$a, $b, $c])->toEqual(vec['a', 'b', 'c']);
    expect(async () ==> await $r->readByteAsync())->toThrow(
      OS\BrokenPipeException::class,
    );

    $r = new IO\BufferedReader(new IO\MemoryHandle('abcdef'));
    expect(await $r->readByteAsync())->toEqual('a');
    expect($r->read(2))->toEqual('bc');
    expect(await $r->readAsync(2))->toEqual('de');
    expect(await $r->readByteAsync())->toEqual('f');

    $r = new IO\BufferedReader(new IO\MemoryHandle('abcdef'));
    expect(await $r->readByteAsync())->toEqual('a');
    expect(await $r->readAllAsync())->toEqual('bcdef');
  }

  public async function testReadFixedSize(): Awaitable<void> {
    $r = new IO\BufferedReader(new IO\MemoryHandle('abcdef'));
    $abc = await $r->readFixedSizeAsync(3);
    $def = await $r->readFixedSizeAsync(3);
    expect(vec[$abc, $def])->toEqual(vec['abc', 'def']);
    expect(async () ==> await $r->readFixedSizeAsync(3))->toThrow(
      OS\BrokenPipeException::class,
    );

    $r = new IO\BufferedReader(new IO\MemoryHandle('abc'));
    expect(async () ==> await $r->readFixedSizeAsync(6))->toThrow(
      OS\BrokenPipeException::class,
    );

    $r = new IO\BufferedReader(new IO\MemoryHandle('abcdef'));
    expect(await $r->readFixedSizeAsync(2))->toEqual('ab');
    expect($r->read(2))->toEqual('cd');
    expect(await $r->readFixedSizeAsync(2))->toEqual('ef');
  }

  public async function testReadTooMuch(): Awaitable<void> {
    $newbuf = () ==> new IO\BufferedReader(new IO\MemoryHandle('abc'));
    expect($newbuf()->read(6))->toEqual('abc');
    expect(await $newbuf()->readAsync(6))->toEqual('abc');
    expect(async () ==> await $newbuf()->readFixedSizeAsync(6))->toThrow(
      OS\BrokenPipeException::class,
    );
  }

  public async function testReadLine(): Awaitable<void> {
    $r = new IO\BufferedReader(new IO\MemoryHandle("ab\ncd\nef"));
    expect(await $r->readLineAsync())->toEqual("ab");
    expect(await $r->readLineAsync())->toEqual("cd");
    expect(async () ==> await $r->readLineAsync())->toThrow(
      OS\BrokenPipeException::class,
    );

    $r = new IO\BufferedReader(new IO\MemoryHandle("ab\ncd\nef"));
    expect(await $r->readLineAsync())->toEqual("ab");
    expect(await $r->readLineAsync())->toEqual("cd");
    expect(await $r->readAllAsync())->toEqual('ef');

    $r = new IO\BufferedReader(new IO\MemoryHandle('ab'));
    expect(async () ==> await $r->readLineAsync())->toThrow(
      OS\BrokenPipeException::class,
    );
  }

  public async function testReadUntil(): Awaitable<void> {
    $r = new IO\BufferedReader(new IO\MemoryHandle("ab\r\ncd\r\n"));
    expect(await $r->readLineAsync())->toEqual("ab\r");
    expect(await $r->readLineAsync())->toEqual("cd\r");

    $r = new IO\BufferedReader(new IO\MemoryHandle("ab\r\ncd\r\n"));
    expect(await $r->readUntilAsync("\r\n"))->toEqual("ab");
    expect(await $r->readUntilAsync("\r\n"))->toEqual("cd");

    $r = new IO\BufferedReader(new IO\MemoryHandle("abFOOcdFOO"));
    expect(await $r->readUntilAsync("FOO"))->toEqual("ab");
    expect(await $r->readUntilAsync("FOO"))->toEqual("cd");

    // Start with genReadByte so we have a non-empty buffer
    $r = new IO\BufferedReader(new IO\MemoryHandle("_abFOOcdFOO"));
    $_ = await $r->readByteAsync();
    expect(await $r->readUntilAsync("FOO"))->toEqual("ab");
    expect(await $r->readUntilAsync("FOO"))->toEqual("cd");
  }

  public async function testEndOfFile(): Awaitable<void> {
    $b = new IO\BufferedReader(new IO\MemoryHandle(''));
    expect($b->isEndOfFile())->toBeTrue();

    // Closed, no data.
    list($r, $w) = IO\pipe();
    $r->close();
    $w->close();
    $b = new IO\BufferedReader($r);
    expect($b->isEndOfFile())->toBeTrue();

    $b = new IO\BufferedReader(new IO\MemoryHandle("foo\nbar\n"));
    expect($b->isEndOfFile())->toBeFalse();
    expect(await $b->readLineAsync())->toEqual("foo");
    expect($b->isEndOfFile())->toBeFalse();
    expect(await $b->readLineAsync())->toEqual("bar");

    expect(async () ==> await $b->readLineAsync())->toThrow(OS\BrokenPipeException::class);
    expect($b->isEndOfFile())->toBeTrue();
  }
}