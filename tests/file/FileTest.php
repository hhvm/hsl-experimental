<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Str;
use namespace HH\Lib\{File, OS};

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class FileTest extends HackTest {
  public async function testExclusiveOpen(): Awaitable<void> {
    /* HH_IGNORE_ERROR[2049] PHP Stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $filename = sys_get_temp_dir().'/'.bin2hex(random_bytes(16));
    $f1 = File\open_write_only($filename, File\WriteMode::MUST_CREATE);
    await $f1->writeAsync('Hello, world!');
    $e = expect(
      () ==> File\open_write_only($filename, File\WriteMode::MUST_CREATE),
    )->toThrow(OS\ErrnoException::class);
    expect($e->getErrno())->toEqual(OS\Errno::EEXIST);
    await $f1->closeAsync();

    $f2 = File\open_read_only($filename);
    $content = await $f2->readAsync();
    await $f2->closeAsync();
    expect($content)->toEqual('Hello, world!');

    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    unlink($filename);
  }

  public async function testTemporaryFile(): Awaitable<void> {
    await using ($tf = File\temporary_file()) {
      $f = $tf->getHandle();
      $path = $f->getPath();
      await $f->writeAsync('Hello, world');
      $content = file_get_contents($path->toString());
      expect($content)->toEqual('Hello, world');
    }
    expect($path->exists())->toBeFalse();
  }

  public async function testMultipleReads(): Awaitable<void> {
    await using ($tf = File\temporary_file()) {
      $f = $tf->getHandle();
      // 10MB is hopefully small enough to not make test infra sad, but
      // way bigger than any reasonable IO buffer size
      $a = Str\repeat('a', 10 * 1024 * 1024);
      $b = Str\repeat('b', 10 * 1024 * 1024);
      $c = Str\repeat('c', 10 * 1024 * 1024);
      await $f->writeAsync($a.$b.$c);

      $fr = File\open_read_only($f->getPath()->toString());
      // FIXME: autoclose
      concurrent {
        $r1 = await $fr->readAsync(10 * 1024 * 1024);
        $r2 = await $fr->readAsync(10 * 1024 * 1024);
        $r3 = await $fr->readAsync(10 * 1024 * 1024);
      }
      // Strong guarantees:
      expect($r1 === $a || $r2 === $a || $r3 === $a)->toBeTrue();
      expect($r1 === $b || $r2 === $b || $r3 === $b)->toBeTrue();
      expect($r1 === $c || $r2 === $c || $r3 === $c)->toBeTrue();
      expect($r1)->toNotEqual($r2);
      expect($r1)->toNotEqual($r3);
      expect($r2)->toNotEqual($r3);
      // NOT GUARANTEED BY HSL API; dependent on eager execution and undefined
      // or semi-defined ordering behavior. Testing here though as we at least
      // want to be aware if an HHVM change changes the behavior here.
      expect($r1)->toEqual($a);
      expect($r2)->toEqual($b);
      expect($r3)->toEqual($c);
    }
  }

  public async function testTruncate(): Awaitable<void> {
    await using $tf = File\temporary_file();
    $f = $tf->getHandle();
    await $f->writeAsync('Hello, world');

    $path = $f->getPath()->toString();
    $fr = File\open_read_only($path);
    $content = await $fr->readAsync();
    await $fr->closeAsync();

    expect($content)->toEqual('Hello, world');

    expect(file_get_contents($path))->toEqual('Hello, world');

    $f = File\open_write_only($path, File\WriteMode::TRUNCATE);
    await $f->writeAsync('Foo bar');
    expect(file_get_contents($path))->toEqual('Foo bar');
    await $f->closeAsync();
  }

  public async function testAppend(): Awaitable<void> {
    await using $tf = File\temporary_file();
    $f = $tf->getHandle();
    await $f->writeAsync('Hello, world');

    $path = $f->getPath()->toString();
    $f = File\open_write_only($path, File\WriteMode::APPEND);
    await $f->writeAsync("\nGoodbye, cruel world");
    await $f->closeAsync();

    expect(file_get_contents($path))->toEqual(
      "Hello, world\nGoodbye, cruel world",
    );
  }

  public async function testLock(): Awaitable<void> {
    await using $tf = File\temporary_file();
    $f = $tf->getHandle();
    $path = $f->getPath()->toString();

    // With a shared lock held open...
    using ($f->tryLockx(File\LockType::SHARED)) {
      $f2 = File\open_read_only($path);
      using ($f2->tryLockx(File\LockType::SHARED)) {
      }
      await $f2->closeAsync();
      // Non-disposable as we need to put it in a lambda
      $f3 = File\open_read_only($path);
      expect(() ==> {
        using ($f3->tryLockx(File\LockType::EXCLUSIVE)) {
        }
      })->toThrow(File\AlreadyLockedException::class);
      await $f3->closeAsync();
    }

    // With an exclusive lock held open...
    using ($f->tryLockx(File\LockType::EXCLUSIVE)) {
      $f4 = File\open_read_only($path);
      expect(() ==> {
        using ($f4->tryLockx(File\LockType::SHARED)) {
        }
      })->toThrow(File\AlreadyLockedException::class);
      await $f4->closeAsync();
    }
  }
}
