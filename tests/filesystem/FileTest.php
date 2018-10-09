<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\Filesystem;

use function Facebook\FBExpect\expect;
use type HH\InvariantException as InvalidRegexException; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTestCase as HackTest;

<<Oncalls('hack')>>
final class FileTest extends HackTest{
  public async function testExclusiveOpen(): Awaitable<void> {
    $filename = \sys_get_temp_dir().'/'.\bin2hex(\random_bytes(16));
    await using $f1 = Filesystem\open_write_only(
      $filename,
      Filesystem\FileWriteMode::MUST_CREATE,
    );
    await $f1->writeAsync('Hello, world!');
    expect(async () ==> {
      await using $f2 = Filesystem\open_write_only(
        $filename,
        Filesystem\FileWriteMode::MUST_CREATE,
      );
    })->toThrow(Filesystem\FileOpenException::class);
    await $f1->closeAsync();

    await using $f2 = Filesystem\open_read_only($filename);
    $content = await $f2->readAsync();
    expect($content)->toBeSame('Hello, world!');

    \unlink($filename);
  }

  public async function testTemporaryFile(): Awaitable<void> {
    await using ($tf = Filesystem\temporary_file()) {
      $path = $tf->getPath();
      await $tf->writeAsync('Hello, world');
      $content = \file_get_contents($path->toString());
      expect($content)->toBeSame('Hello, world');
    }
    expect($path->exists())->toBeFalse();
  }

  public async function testTruncate(): Awaitable<void> {
    await using $tf = Filesystem\temporary_file();
    await $tf->writeAsync('Hello, world');
    await $tf->seekAsync(0);

    $content = await $tf->readAsync();
    expect($content)->toBeSame('Hello, world');
    await $tf->closeAsync();

    $path = $tf->getPath()->toString();
    expect(\file_get_contents($path))->toBeSame('Hello, world');

    await using $f =
      Filesystem\open_write_only($path, Filesystem\FileWriteMode::TRUNCATE);
    await $f->writeAsync('Foo bar');
    await $f->closeAsync();

    expect(\file_get_contents($path))->toBeSame('Foo bar');
  }

  public async function testAppend(): Awaitable<void> {
    await using $tf = Filesystem\temporary_file();
    await $tf->writeAsync('Hello, world');
    await $tf->closeAsync();

    $path = $tf->getPath()->toString();
    await using $f =
      Filesystem\open_write_only($path, Filesystem\FileWriteMode::APPEND);
    await $f->writeAsync("\nGoodbye, cruel world");
    await $f->closeAsync();

    expect(\file_get_contents($path))->toBeSame(
      "Hello, world\nGoodbye, cruel world",
    );
  }

}
