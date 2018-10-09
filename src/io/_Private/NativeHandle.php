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
abstract class NativeHandle implements IO\ReadWriteHandle {
  protected function __construct(private resource $impl) {
    \stream_set_blocking($impl, false);
  }

  private ?Awaitable<mixed> $lastOperation;
  private function queuedAsync<T>(
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
    invariant(
      $max_bytes === null || $max_bytes >= 0,
      '$max_bytes must be null, or >= 0',
    );
    if ($max_bytes === 0) {
      return '';
    }
    $result = \stream_get_contents($this->impl, $max_bytes ?? -1);
    if ($result === false) {
      throw new IO\ReadException();
    }
    return $result as string;
  }

  final public function readAsync(?int $max_bytes = null): Awaitable<string> {
    invariant(
      $max_bytes === null || $max_bytes >= 0,
      '$max_bytes must be null, or >= 0',
    );

    return $this->queuedAsync(async () ==> {
      $data = '';
      while (($max_bytes === null || $max_bytes > 0) && !$this->isEndOfFile()) {
        $chunk = $this->rawReadBlocking($max_bytes);
        $data .= $chunk;
        if ($max_bytes !== null) {
          $max_bytes -= Str\length($chunk);
        }
        if ($max_bytes === null || $max_bytes > 0) {
          await \stream_await($this->impl, \STREAM_AWAIT_READ);
        }
      }
      return $data;
    });
  }

  final public function readBlocking(?int $max_bytes = null): string {
    return \HH\Asio\join($this->readAsync());
  }

  final public function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    invariant(
      $max_bytes === null || $max_bytes >= 0,
      '$max_bytes must be null, or >= 0',
    );

    return $this->queuedAsync(async () ==> {
      if ($max_bytes === null) {
        // The placeholder value for 'default' is not documented
        $impl = () ==> \fgets($this->impl);
      } else {
        // ... but if you specify a value, it returns 1 less.
        $impl = () ==> \fgets($this->impl, $max_bytes + 1);
      }
      $data = $impl();
      while ($data === false && !$this->isEndOfFile()) {
        await \stream_await($this->impl, \STREAM_AWAIT_READ);
        $data = $impl();
      }
      return $data === false ? '' : $data;
    });
  }

  final public function readLineBlocking(?int $max_bytes = null): string {
    return \HH\Asio\join($this->readLineAsync($max_bytes));
  }

  final public function rawWriteBlocking(string $bytes): int {
    $result = \fwrite($this->impl, $bytes);
    if ($result === false) {
      throw new IO\WriteException();
    }
    return $result as int;
  }


  final public function writeAsync(string $bytes): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      while ($bytes !== '') {
        $written = $this->rawWriteBlocking($bytes);
        $bytes = Str\slice($bytes, $written);
        await \stream_await($this->impl, \STREAM_AWAIT_WRITE);
      }
    });
  }

  final public function writeBlocking(string $bytes): void {
    \HH\Asio\join($this->writeAsync($bytes));
  }

  final public function flushAsync(): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      \fflush($this->impl);
    });
  }

  final public function flushBlocking(): void {
    \HH\Asio\join($this->flushAsync());
  }

  final public function isEndOfFile(): bool {
    return \feof($this->impl);
  }

  final public async function closeAsync(): Awaitable<void> {
    await $this->flushAsync();
    \fclose($this->impl);
  }

  final public function closeBlocking(): void {
    \HH\Asio\join($this->closeAsync());
  }
}
