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

  final public function rawWriteBlocking(string $bytes): int {
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

  final public function writeAsync(
    string $bytes,
    ?float $timeout_seconds = null,
  ): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      /* HH_IGNORE_ERROR[2049] */
      /* HH_IGNORE_ERROR[4107] */
      $start = \microtime(true);
      while (true) {
        $written = $this->rawWriteBlocking($bytes);
        $bytes = Str\slice($bytes, $written);
        if ($bytes === '') {
          break;
        }
        if ($timeout_seconds is float) {
          /* HH_IGNORE_ERROR[2049] */
          /* HH_IGNORE_ERROR[4107] */
          $now = \microtime(true);
          $timeout_seconds -= ($now - $start);
          if ($timeout_seconds < 0.0) {
            _OS\throw_errorcode(OS\ErrorCode::ETIMEDOUT, __METHOD__);
          }
          $start = $now;
        }
        await $this->selectAsync(\STREAM_AWAIT_WRITE, $timeout_seconds);
      }
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
