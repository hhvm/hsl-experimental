<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\IO;

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

/** Test pipes specifically, and core IO functions.
 *
 * This is basic coverage for all `LegacyPHPResourceHandle`s
 */
// @oss-disable: <<Oncalls('hack')>>
final class PipeTest extends HackTest {
  public async function testWritesAreReadableAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    await $w->writeAsync("Hello, world!\nHerp derp\n");

    $read = await $r->readLineAsync();
    expect($read)->toEqual("Hello, world!\n");

    $read = await $r->readLineAsync();
    expect($read)->toEqual("Herp derp\n");

    expect($r->isEndOfFile())->toBeFalse();
    await $w->closeAsync();
    $s = await $r->readAllAsync();
    expect($s)->toEqual('');
    expect($r->isEndOfFile())->toBeTrue();
  }

  public async function testReadAllAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    await $w->writeAsync("Hello, world!\nHerp derp\n");
    await $w->closeAsync();
    $s = await $r->readAllAsync();
    expect($s)->toEqual("Hello, world!\nHerp derp\n");
  }

  public async function testPartialReadAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    await $w->writeAsync('1234567890');
    $s = await $r->readAllAsync(5);
    expect($s)->toEqual('12345');
    $s = await $r->readAllAsync(5);
    expect($s)->toEqual('67890');
  }

  public async function testPartialReadLineAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    await $w->writeAsync("1234567890\n12345\n67890\n");
    $s = await $r->readLineAsync(5);
    expect($s)->toEqual('12345');
    $s = await $r->readLineAsync(5);
    expect($s)->toEqual('67890');
    $s = await $r->readLineAsync();
    expect($s)->toEqual("\n");
    $s = await $r->readLineAsync(10);
    expect($s)->toEqual("12345\n");
  }

  public async function testPartialRead(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    concurrent {
      await async {
        await $w->writeAsync('foo bar');
        await \HH\Asio\later();
        await $w->writeAsync('herp derp');
      };
      $read = await $r->readPartialAsync();
    }
    expect($read)->toEqual('foo bar');
  }

  public async function testFullRead(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    concurrent {
      await async {
        await $w->writeAsync('foo bar');
        await \HH\Asio\later();
        await $w->writeAsync('herp derp');
        await $w->closeAsync();
      };
      $read = await $r->readAllAsync();
    }
    expect($read)->toEqual('foo barherp derp');
  }


  public async function testReadTooManyAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_nd();
    await $w->writeAsync('1234567890');
    await $w->closeAsync();
    $s = await $r->readAllAsync(11);
    expect($s)->toEqual('1234567890');
  }

  public async function testInteractionAsync(): Awaitable<void> {
    // Emulate a client-server environment
    list($cr, $sw) = IO\pipe_nd();
    list($sr, $cw) = IO\pipe_nd();

    concurrent {
      await async { // client
        await $cw->writeAsync("Herp\n");
        $response = await $cr->readLineAsync();
        expect($response)->toEqual("Derp\n");
        await $cw->writeAsync("Foo\n");
        $response = await $cr->readLineAsync();
        expect($response)->toEqual("Bar\n");
      };
      await async { // server
        $request = await $sr->readLineAsync();
        expect($request)->toEqual("Herp\n");
        await $sw->writeAsync("Derp\n");
        $request = await $sr->readLineAsync();
        expect($request)->toEqual("Foo\n");
        await $sw->writeAsync("Bar\n");
      };
    }
  }
}
