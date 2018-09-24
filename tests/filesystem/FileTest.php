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
use type Facebook\HackTest\HackTestCase; // @oss-enable
use type HH\InvariantException as InvalidRegexException; // @oss-enable

<<Oncalls('hack')>>
final class FileTest extends HackTestCase {
  public async function testExclusiveOpen(): Awaitable<void> {
    $filename = \sys_get_temp_dir().'/'.\bin2hex(\random_bytes(16));
    $f1 = Filesystem\open_write_only(
      $filename,
      Filesystem\FileWriteMode::MUST_CREATE,
    );
    await $f1->writeAsync('Hello, world!');
    expect(() ==> {
      $f2 = Filesystem\open_write_only(
        $filename,
        Filesystem\FileWriteMode::MUST_CREATE,
      );
    })->toThrow(Filesystem\FileOpenException::class);
    await $f1->closeAsync();

    $f2 = Filesystem\open_read_only($filename);
    $content = await $f2->readAsync();
    expect($content)->toBeSame('Hello, world!');

    \unlink($filename);
  }

  public async function testTemporaryFile(): Awaitable<void> {
    await using ($tf = new Filesystem\TemporaryFile()) {
      $f = $tf->getHandle();
      $path = $f->getPath();
      await $f->writeAsync('Hello, world');
      $content = \file_get_contents($path->toString());
      expect($content)->toBeSame('Hello, world');
    }
    expect($path->exists())->toBeFalse();
  }

  public async function testTruncate(): Awaitable<void> {
    await using ($tf = new Filesystem\TemporaryFile());
    $f = $tf->getHandle();
    await $f->writeAsync('Hello, world');
    await $f->seekAsync(0);

    $content = await $f->readAsync();
    expect($content)->toBeSame('Hello, world');
    await $f->closeAsync();

    $path = $f->getPath()->toString();
    expect(\file_get_contents($path))->toBeSame('Hello, world');

    $f = Filesystem\open_write_only($path, Filesystem\FileWriteMode::TRUNCATE);
    await $f->writeAsync('Foo bar');
    await $f->closeAsync();

    expect(\file_get_contents($path))->toBeSame('Foo bar');
  }

	public async function testAppend(): Awaitable<void> {
		await using ($tf = new Filesystem\TemporaryFile());
		$f = $tf->getHandle();
		await $f->writeAsync('Hello, world');
		await $f->closeAsync();

    $path = $f->getPath()->toString();
    $f = Filesystem\open_write_only($path, Filesystem\FileWriteMode::APPEND);
    await $f->writeAsync("\nGoodbye, cruel world");
    await $f->closeAsync();

    expect(\file_get_contents($path))->toBeSame(
      "Hello, world\nGoodbye, cruel world",
    );
	}

}
