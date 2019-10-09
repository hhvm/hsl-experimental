<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\{Experimental\IO, Str};

trait LegacyPHPResourceWriteHandleTrait implements IO\WriteHandle {
  require extends LegacyPHPResourceHandle;

  final public function rawWriteBlocking(string $bytes): int {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \fwrite($this->impl, $bytes);
    if ($result === false) {
      throw new IO\WriteException();
    }
    return $result as int;
  }

  final public function writeAsync(string $bytes): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      while (true) {
        $written = $this->rawWriteBlocking($bytes);
        $bytes = Str\slice($bytes, $written);
        if ($bytes === '') {
          break;
        }
        await $this->selectAsync(\STREAM_AWAIT_WRITE);
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
