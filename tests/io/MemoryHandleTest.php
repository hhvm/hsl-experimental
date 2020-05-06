<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\{IO, OS, Str};

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class MemoryHandleTest extends HackTest {
  public async function testRead(): Awaitable<void> {
    $h = new IO\MemoryHandle('herpderp');
    expect($h->read(4))->toEqual('herp');
    expect(await $h->readAllAsync())->toEqual('derp');
    expect(await $h->readAllAsync())->toEqual('');
    expect($h->tell())->toEqual(8);
    await $h->seekAsync(0);
    expect($h->tell())->toEqual(0);
    expect(await $h->readAllAsync())->toEqual('herpderp');
    await $h->seekAsync(4);
    expect($h->tell())->toEqual(4);
    expect(await $h->readAllAsync())->toEqual('derp');
  }

  public async function testReadAtInvalidOffset(): Awaitable<void> {
    $h = new IO\MemoryHandle('herpderp');
    await $h->seekAsync(99999);
    expect(await $h->readAllAsync())->toEqual('');
  }

  public async function testReadTooMuch(): Awaitable<void> {
    $h = new IO\MemoryHandle("herpderp");
    expect(async () ==> await $h->readFixedSizeAsync(1024))->toThrow(
      OS\BrokenPipeException::class,
    );
  }

  public function testWrite(): void {
    $h = new IO\MemoryHandle();
    $h->write('herp');
    expect($h->getBuffer())->toEqual('herp');
    $h->write('derp');
    expect($h->getBuffer())->toEqual('herpderp');
    $h->reset();
    $h->write('foo');
    expect($h->getBuffer())->toEqual('foo');
  }

  public async function testOverwrite(): Awaitable<void> {
    $h = new IO\MemoryHandle('xxxxderp');
    $h->write('herp');
    expect($h->getBuffer())->toEqual('herpderp');
    expect(await $h->readAllAsync())->toEqual('derp');
    await $h->seekAsync(0);
    expect(await $h->readAllAsync())->toEqual('herpderp');
  }

  public async function testAppend(): Awaitable<void> {
    $h = new IO\MemoryHandle('herp', IO\MemoryHandleWriteMode::APPEND);
    $h->write('derp');
    expect($h->getBuffer())->toEqual('herpderp');
    expect(await $h->readAllAsync())->toEqual('');
    await $h->seekAsync(0);
    expect(await $h->readAllAsync())->toEqual('herpderp');
  }

	public async function testReset(): Awaitable<void> {
    $h = new IO\MemoryHandle('herpderp');
		expect(await $h->readAllAsync())->toEqual('herpderp');
    $h->reset('foobar');
		expect(await $h->readAllAsync())->toEqual('foobar');
    await $h->seekAsync(0);
		expect(await $h->readAllAsync())->toEqual('foobar');
	}

}
