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

use namespace HH\Lib\{IO, Str, OS, Math};
use namespace HH\Lib\_Private\_OS;

trait FileDescriptorReadHandleTrait implements IO\ReadHandle {
  require extends FileDescriptorHandle;

  final public function rawReadBlocking(?int $max_bytes = null): string {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($max_bytes === 0) {
      return '';
    }
    return OS\read($this->impl, $max_bytes ?? 0);
  }

  private string $readBuffer = '';

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
    $timeout_nanos = $timeout_seconds === null
      ? 0
      : (int) Math\ceil($timeout_seconds * 1000 * 1000 * 1000);

    $max_bytes ??= 1024;

    $data = Str\slice($this->readBuffer, 0, $max_bytes);
    $this->readBuffer = Str\length($this->readBuffer) > $max_bytes
      ? Str\slice($this->readBuffer, $max_bytes)
      : '';

    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $start = \clock_gettime_ns(\CLOCK_MONOTONIC);
    while ($max_bytes > 0) {
      $chunk = $this->rawReadBlocking($max_bytes);
      $data .= $chunk;
      $max_bytes -= Str\length($chunk);

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
      if ($max_bytes > 0) {
        $chunk = $this->rawReadBlocking($max_bytes);
        if ($chunk === '') {
          // EOF
          break;
        }
        $data .= $chunk;
        $max_bytes -= Str\length($chunk);
        if ($max_bytes <= 0) {
          break;
        }
        await $this->selectAsync(\STREAM_AWAIT_READ, $timeout_nanos);
      }
    }
    return $data;
  }

  final public async function readLineAsync(
    ?int $max_bytes = null,
    ?float $timeout_seconds = null,
  ): Awaitable<string> {
    return '';
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($timeout_seconds is float && $timeout_seconds < 0) {
      throw new \InvalidArgumentException(
        '$timeout_seconds must be null, or >= 0',
      );
    }
    $max_bytes ??= 1024;

    $idx = Str\search($this->readBuffer, "\n");
    if ($idx is nonnull) {
      $idx++;
      if ($idx >= $max_bytes) {
        $ret = Str\slice($this->readBuffer, 0, $max_bytes);
        $this->readbuffer = Str\slice($this->readBuffer, $max_bytes);
        return $ret;
      }
      $ret = Str\slice($this->readBuffer, 0, $idx);
      $this->readBuffer = Str\slice($this->readBuffer, $idx);
      return $ret;
    }

    $buf = $this->readBuffer;

    if (Str\length($buf) >= $max_bytes) {
      $this->readBuffer = Str\slice($buf, $max_bytes);
      return Str\slice($buf, 0, $max_bytes);
    }

    $buf .= await $this->readAsync(
      $max_bytes - Str\length($buf),
      $timeout_seconds,
    );

    $idx = Str\search($buf, "\n");
    if ($idx === null) {
      return $buf;
    }
    $idx++;
    $this->readBuffer = Str\slice($buf, $idx);
    return Str\slice($buf, 0, $idx);
  }
}
