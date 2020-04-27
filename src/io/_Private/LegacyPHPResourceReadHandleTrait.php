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
use namespace HH\Lib\{IO, OS};
use namespace HH\Lib\_Private\_OS;
use type HH\Lib\_Private\PHPWarningSuppressor;

trait LegacyPHPResourceReadHandleTrait implements IO\ReadHandle {
  require extends LegacyPHPResourceHandle;

  final public function read(?int $max_bytes = null): string {
    $max_bytes ??= DEFAULT_READ_BUFFER_SIZE;

    if ($max_bytes <= 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or > 0');
    }

    using new PHPWarningSuppressor();

    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \stream_get_contents($this->impl, $max_bytes ?? -1);
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $errno = \posix_get_last_error();
    if ($result === false) {
      _OS\throw_errno($errno as int, 'stream_get_contents');
    }
    return $result as string;
  }

  final public async function readAsync(
    ?int $max_bytes = null,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    $max_bytes ??= DEFAULT_READ_BUFFER_SIZE;

    if ($max_bytes <= 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or > 0');
    }
    if ($timeout_ns is int && $timeout_ns <= 0) {
      throw new \InvalidArgumentException('$timeout_ns must be null, or > 0');
    }
    $timeout_ns ??= 0;

    $chunk = $this->read($max_bytes);
    if ($chunk !== '') {
      return $chunk;
    }
    $timeout_secs = $timeout_ns * 1.0E-9;
    await $this->selectAsync(\STREAM_AWAIT_READ, $timeout_secs);
    return $this->read($max_bytes);
  }
}
