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
use namespace HH\Lib\{Str, Tuple};

use function Facebook\FBExpect\expect;
use type HH\InvariantException as InvalidRegexException; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class FileTest extends HackTest {
  public async function testExclusiveOpen(): Awaitable<void> {
    /* HH_IGNORE_ERROR[2049] PHP Stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $filename = sys_get_temp_dir().'/'.bin2hex(random_bytes(16));
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

    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    unlink($filename);
  }

  public async function testTemporaryFile(): Awaitable<void> {
    await using ($tf = Filesystem\temporary_file()) {
      $path = $tf->getPath();
      await $tf->writeAsync('Hello, world');
      $content = file_get_contents($path->toString());
      expect($content)->toBeSame('Hello, world');
    }
    expect($path->exists())->toBeFalse();
  }

  public async function testMultipleReads(): Awaitable<void> {
    await using ($tf = Filesystem\temporary_file()) {
      // 10MB is hopefully small enough to not make test infra sad, but
      // way bigger than any reasonable IO buffer size
      $a = Str\repeat('a', 10 * 1024 * 1024);
      $b = Str\repeat('b', 10 * 1024 * 1024);
      $c = Str\repeat('c', 10 * 1024 * 1024);
      await $tf->writeAsync($a.$b.$c);
      await $tf->flushAsync();

      await using (
        $tfr = Filesystem\open_read_only($tf->getPath()->toString())
      ) {
        list($r1, $r2, $r3) = await Tuple\from_async(
          $tfr->readAsync(10 * 1024 * 1024),
          $tfr->readAsync(10 * 1024 * 1024),
          $tfr->readAsync(10 * 1024 * 1024),
        );
      }
      // Strong guarantees:
      expect($r1 === $a || $r2 === $a || $r3 === $a)->toBeTrue();
      expect($r1 === $b || $r2 === $b || $r3 === $b)->toBeTrue();
      expect($r1 === $c || $r2 === $c || $r3 === $c)->toBeTrue();
      expect($r1)->toNotBeSame($r2);
      expect($r1)->toNotBeSame($r3);
      expect($r2)->toNotBeSame($r3);
      // NOT GUARANTEED BY HSL API; dependent on eager execution and undefined
      // or semi-defined ordering behavior. Testing here though as we at least
      // want to be aware if an HHVM change changes the behavior here.
      expect($r1)->toBeSame($a);
      expect($r2)->toBeSame($b);
      expect($r3)->toBeSame($c);
    }
  }

  public async function testTruncate(): Awaitable<void> {
    await using $tf = Filesystem\temporary_file();
    await $tf->writeAsync('Hello, world');
    await $tf->flushAsync();

    $path = $tf->getPath()->toString();
    await using ($tfr = Filesystem\open_read_only($path)) {
      $content = await $tfr->readAsync();
    }
    expect($content)->toBeSame('Hello, world');
    await $tf->closeAsync();

    expect(file_get_contents($path))->toBeSame('Hello, world');

    await using $f =
      Filesystem\open_write_only($path, Filesystem\FileWriteMode::TRUNCATE);
    await $f->writeAsync('Foo bar');
    await $f->closeAsync();

    expect(file_get_contents($path))->toBeSame('Foo bar');
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

    expect(file_get_contents($path))->toBeSame(
      "Hello, world\nGoodbye, cruel world",
    );
  }

}
