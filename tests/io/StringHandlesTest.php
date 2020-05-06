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
final class StringHandlesTest extends HackTest {
  public async function testStringInput(): Awaitable<void> {
    $h = new IO\StringInput('herpderp');
    expect($h->read(4))->toEqual('herp');
    expect(await $h->readAllAsync())->toEqual('derp');
    expect(await $h->readAllAsync())->toEqual('');
  }

  public async function testStringOutput(): Awaitable<void> {
    $h = new IO\StringOutput();
    $h->write('herp');
    expect($h->getBuffer())->toEqual('herp');
    $h->write('derp');
    expect($h->getBuffer())->toEqual('herpderp');
    $h->clearBuffer();
    $h->write('foo');
    expect($h->getBuffer())->toEqual('foo');
  }
}
