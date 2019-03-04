<?hh // strict
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

<<__Sealed(FileHandle::class, PipeHandle::class, StdioHandle::class)>>
abstract class NativeHandle implements IO\ReadHandle, IO\WriteHandle {
  private bool $isAwaitable = true;
  protected function __construct(private resource $impl) {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \stream_set_blocking($impl, false);
  }

  private ?Awaitable<mixed> $lastOperation;
  protected function queuedAsync<T>(
    (function(): Awaitable<T>) $next,
  ): Awaitable<T> {
    $last = $this->lastOperation;
    $queue = async {
      await $last;
      return await $next();
    };
    $this->lastOperation = $queue;
    return $queue;
  }

  final public function rawReadBlocking(?int $max_bytes = null): string {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException(
        '$max_bytes must be null, or >= 0',
      );
    }
    if ($max_bytes === 0) {
      return '';
    }
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \stream_get_contents($this->impl, $max_bytes ?? -1);
    if ($result === false) {
      throw new IO\ReadException();
    }
    return $result as string;
  }

  private async function selectAsync(int $flags): Awaitable<void> {
    if (!$this->isAwaitable) {
      return;
    }
    if ($this->isEndOfFile()) {
      return;
    }
    try {
      /* HH_FIXME[2049] *not* PHP stdlib */
      /* HH_FIXME[4107] *not* PHP stdlib */
      await \stream_await($this->impl, $flags);
    } catch (\InvalidOperationException $_) {
      // e.g. real files on Linux when using epoll
      $this->isAwaitable = false;
    }
  }

  final public async function readAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException(
        '$max_bytes must be null, or >= 0',
      );
    }

    $data = '';
    while (($max_bytes === null || $max_bytes > 0) && !$this->isEndOfFile()) {
      $chunk = $this->rawReadBlocking($max_bytes);
      $data .= $chunk;
      if ($max_bytes !== null) {
        $max_bytes -= Str\length($chunk);
      }
      if ($max_bytes === null || $max_bytes > 0) {
        await $this->selectAsync(\STREAM_AWAIT_READ);
      }
    }
    return $data;
  }

  final public async function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException(
        '$max_bytes must be null, or >= 0',
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
    while ($data === false && !$this->isEndOfFile()) {
      await $this->selectAsync(\STREAM_AWAIT_READ);
      $data = $impl();
    }
    return $data === false ? '' : $data;
  }

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
      /* HH_IGNORE_ERROR[2049] */
      /* HH_IGNORE_ERROR[4107] */
      @\fflush($this->impl);
    });
  }

  final public function isEndOfFile(): bool {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return \feof($this->impl);
  }

  final public async function closeAsync(): Awaitable<void> {
    await $this->flushAsync();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    @\fclose($this->impl);
  }
}
