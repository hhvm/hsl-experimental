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

use namespace HH\Lib\Tuple;

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTestCase as HackTest;

/** Test pipes specifically, and core IO functions.
 *
 * This is basic coverage for all `NativeHandle`s
 */
<<Oncalls('hack')>>
final class PipeTest extends HackTest {
  public async function testWritesAreReadableAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_non_disposable();
    await $w->writeAsync("Hello, world!\nHerp derp\n");

    $read = await $r->readLineAsync();
    expect($read)->toBeSame("Hello, world!\n");

    $read = await $r->readLineAsync();
    expect($read)->toBeSame("Herp derp\n");

    expect($r->isEndOfFile())->toBeFalse();
    await $w->closeAsync();
    $s = await $r->readAsync();
    expect($s)->toBeSame('');
    expect($r->isEndOfFile())->toBeTrue();
  }

  public async function testReadAllAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_non_disposable();
    await $w->writeAsync("Hello, world!\nHerp derp\n");
    await $w->closeAsync();
    $s = await $r->readAsync();
    expect($s)->toBeSame("Hello, world!\nHerp derp\n");
  }

  public async function testPartialReadAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_non_disposable();
    await $w->writeAsync('1234567890');
    $s = await $r->readAsync(5);
    expect($s)->toBeSame('12345');
    $s = await $r->readAsync(5);
    expect($s)->toBeSame('67890');
  }

  public async function testPartialReadLineAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_non_disposable();
    await $w->writeAsync("1234567890\n12345\n67890\n");
    $s = await $r->readLineAsync(5);
    expect($s)->toBeSame('12345');
    $s = await $r->readLineAsync(5);
    expect($s)->toBeSame('67890');
    $s = await $r->readLineAsync();
    expect($s)->toBeSame("\n");
    $s = await $r->readLineAsync(10);
    expect($s)->toBeSame("12345\n");
  }

  public async function testReadTooManyAsync(): Awaitable<void> {
    list($r, $w) = IO\pipe_non_disposable();
    await $w->writeAsync('1234567890');
    await $w->closeAsync();
    $s = await $r->readAsync(11);
    expect($s)->toBeSame('1234567890');
  }

  public async function testInteractionAsync(): Awaitable<void> {
    // Emulate a client-server environment
    list($cr, $sw) = IO\pipe_non_disposable();
    list($sr, $cw) = IO\pipe_non_disposable();

    await Tuple\from_async(
      async { // client
        await $cw->writeAsync("Herp\n");
        $response = await $cr->readLineAsync();
        expect($response)->toBeSame("Derp\n");
        await $cw->writeAsync("Foo\n");
        $response = await $cr->readLineAsync();
        expect($response)->toBeSame("Bar\n");
      },
      async { // server
        $request = await $sr->readLineAsync();
        expect($request)->toBeSame("Herp\n");
        await $sw->writeAsync("Derp\n");
        $request = await $sr->readLineAsync();
        expect($request)->toBeSame("Foo\n");
        await $sw->writeAsync("Bar\n");
      },
    );
  }
}
