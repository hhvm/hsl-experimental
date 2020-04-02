<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\IO;
use namespace HH\Lib\File;

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class CopyTest extends HackTest {
  public async function testCopy(): Awaitable<void> {
    await using $source = File\temporary_file();
    await using $target = File\temporary_file();

    await $source->writeAsync('Hello, World!');
    await $source->seekAsync(0);

    expect($source->isEndOfFile())->toBeFalse();
    expect($target->isEndOfFile())->toBeFalse();

    expect(await $target->readAsync())->toBeSame('');

    await IO\copy($source, $target);

    expect($source->isEndOfFile())->toBeTrue();
    expect($target->isEndOfFile())->toBeTrue();

    // Assert that the content has been copied.
    await $target->seekAsync(0);
    expect(await $target->readAsync())->toBeSame('Hello, World!');

    // Assert that the original file contains the same content. not modifications are made.
    await $source->seekAsync(0);
    expect(await $source->readAsync())->toBeSame('Hello, World!');
  }
}
