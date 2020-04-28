<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_IO;

use namespace HH\Lib\Str;
use namespace HH\Lib\_Private\_OS;
use namespace HH\Lib\{IO, OS};
use type HH\Lib\_Private\PHPWarningSuppressor;

trait LegacyPHPResourceWriteHandleTrait implements IO\WriteHandle {
  require extends LegacyPHPResourceHandle;

  final public function write(string $bytes): int {
    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \fwrite($this->impl, $bytes);
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $errno = \posix_get_last_error();
    if ($result === false) {
      _OS\throw_errno($errno, 'fwrite');
    }
    return $result as int;
  }

  final public async function writeAsync(
    string $bytes,
    ?int $timeout_ns = null,
  ): Awaitable<int> {
    if ($timeout_ns is int && $timeout_ns <= 0) {
      throw new \InvalidArgumentException('$timeout_ns must be null, or >= 0');
    }
    $timeout_ns ??= 0;
    $timeout_secs = $timeout_ns * 1.0E-9;

    return await $this->queuedAsync(async () ==> {
      try {
        return $this->write($bytes);
      } catch (OS\BlockingIOException $_) {
        // need to wait
      }
      await $this->selectAsync(\STREAM_AWAIT_WRITE, $timeout_secs);
      return $this->write($bytes);
    });
  }

  final public function flushAsync(): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      using new PHPWarningSuppressor();
      /* HH_IGNORE_ERROR[2049] */
      /* HH_IGNORE_ERROR[4107] */
      \fflush($this->impl);
    });
  }
}
