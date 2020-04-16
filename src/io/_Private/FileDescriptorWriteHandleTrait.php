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

use namespace HH\Lib\{IO, Math, OS, Str};
use namespace HH\Lib\_Private\_OS;

trait FileDescriptorWriteHandleTrait implements IO\WriteHandle {
  require extends FileDescriptorHandle;

  final public function rawWriteBlocking(string $bytes): int {
    return OS\write($this->impl, $bytes);
  }

  final public function writeAsync(
    string $bytes,
    ?float $timeout_seconds = null,
  ): Awaitable<void> {
    $timeout_nanos = $timeout_seconds is null
      ? 0
      : (int) Math\ceil($timeout_seconds * 1000 * 1000 * 1000);
    return $this->queuedAsync(async () ==> {
      /* HH_IGNORE_ERROR[2049] PHP stdlib */
      /* HH_IGNORE_ERROR[4107] PHP stdlib */
      $start = \clock_gettime_ns(\CLOCK_MONOTONIC);
      while (true) {
        $written = $this->rawWriteBlocking($bytes);
        $bytes = Str\slice($bytes, $written);
        if ($bytes === '') {
          break;
        }
        if ($timeout_nanos > 0) {
          /* HH_IGNORE_ERROR[2049] PHP stdlib */
          /* HH_IGNORE_ERROR[4107] PHP stdlib */
          $now = \clock_gettime_ns(\CLOCK_MONOTONIC);
          $timeout_nanos -= ($now - $start);
          if ($timeout_nanos < 0) {
            _OS\throw_errno(OS\Errno::ETIMEDOUT, __METHOD__);
          }
          $start = $now;
        }
        await $this->selectAsync(\STREAM_AWAIT_WRITE, $timeout_nanos);
      }
    });
  }

  final public function flushAsync(): Awaitable<void> {
    // no write buffer, so just wait for end of queue.
    return $this->queuedAsync(async () ==> {});
  }
}
