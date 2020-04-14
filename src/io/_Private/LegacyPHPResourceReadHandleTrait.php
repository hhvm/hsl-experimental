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

  final public function rawReadBlocking(?int $max_bytes = null): string {
    using new PHPWarningSuppressor();
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($max_bytes === 0) {
      return '';
    }
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
    ?float $timeout_seconds = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($timeout_seconds is float && $timeout_seconds < 0.0) {
      throw new \InvalidArgumentException('$timeout_seconds be null, or >= 0');
    }

    $data = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $start = \microtime(true);
    while (($max_bytes === null || $max_bytes > 0) && !$this->isEndOfFile()) {
      $chunk = $this->rawReadBlocking($max_bytes);
      $data .= $chunk;
      if ($max_bytes !== null) {
        $max_bytes -= Str\length($chunk);
      }
      if ($timeout_seconds is float) {
        /* HH_IGNORE_ERROR[2049] __PHPStdLib */
        /* HH_IGNORE_ERROR[4107] __PHPStdLib */
        $now = \microtime(true);
        $timeout_seconds -= ($now - $start);
        if ($timeout_seconds < 0) {
          _OS\throw_errno(OS\Errno::ETIMEDOUT, __METHOD__);
        }
        $start = $now;
      }
      if ($max_bytes === null || $max_bytes > 0) {
        await $this->selectAsync(\STREAM_AWAIT_READ, $timeout_seconds);
      }
    }
    return $data;
  }

  final public async function readLineAsync(
    ?int $max_bytes = null,
    ?float $timeout_seconds = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($timeout_seconds is float && $timeout_seconds < 0) {
      throw new \InvalidArgumentException(
        '$timeout_seconds must be null, or >= 0',
      );
    }

    if ($max_bytes === null) {
      // The placeholder value for 'default' is not documented
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $impl = () ==> \fgets($this->impl);
    } else {
      // ... but if you specify a value, it returns 1 less.
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $impl = () ==> \fgets($this->impl, $max_bytes + 1);
    }
    $data = $impl();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $start = \microtime(true);
    while ($data === false && !$this->isEndOfFile()) {
      if ($timeout_seconds is float) {
        /* HH_IGNORE_ERROR[2049] __PHPStdLib */
        /* HH_IGNORE_ERROR[4107] __PHPStdLib */
        $now = \microtime(true);
        $timeout_seconds -= ($now - $start);
        if ($timeout_seconds < 0.0) {
          _OS\throw_errno(OS\Errno::ETIMEDOUT, __METHOD__);
        }
        $start = $now;
      }
      await $this->selectAsync(\STREAM_AWAIT_READ, $timeout_seconds);
      $data = $impl();
    }
    return $data === false ? '' : $data;
  }
}
