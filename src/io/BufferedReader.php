<?hh
/*
 *  Copyright (c) 2017-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\IO;

use namespace HH\Lib\{IO, OS, Str};
use namespace HH\Lib\_Private\_OS;

/** Wrapper for `ReadHandle`s, with buffered line-based byte-based accessors.
 *
 * - `readLineAsync()` is similar to `fgets()`
 * - `readByteAsync()` is similar to `fgetc()`
 */
final class BufferedReader implements IO\ReadHandle {
  use ReadHandleConvenienceMethodsTrait;

  public function __construct(private IO\ReadHandle $handle) {
  }

  public function getHandle(): IO\ReadHandle {
    return $this->handle;
  }

  private bool $eof = false;
  private string $buffer = '';

  // implementing interface
  public function read(?int $max_bytes = null): string {
    if ($max_bytes is int && $max_bytes <= 0) {
      _OS\throw_errno(
        OS\Errno::EINVAL,
        "Max bytes must be null, or greater than 0",
      );
    }

    if ($this->eof) {
      return '';
    }
    if ($this->buffer === '') {
      $this->buffer = $this->getHandle()->read();
      if ($this->buffer === '') {
        $this->eof = true;
        return '';
      }
    }

    if ($max_bytes is null || $max_bytes >= Str\length($this->buffer)) {
      $buf = $this->buffer;
      $this->buffer = '';
      return $buf;
    }
    $buf = $this->buffer;
    $this->buffer = Str\slice($buf, $max_bytes);
    return Str\slice($buf, 0, $max_bytes);
  }

  public async function readAsync(
    ?int $max_bytes = null,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes <= 0) {
      _OS\throw_errno(
        OS\Errno::EINVAL,
        "Max bytes must be null, or greater than 0",
      );
    }
    if ($timeout_ns is int && $timeout_ns <= 0) {
      _OS\throw_errno(
        OS\Errno::EINVAL,
        "Timeout must be null, or greater than 0",
      );
    }

    if ($this->eof) {
      return '';
    }
    if ($this->buffer === '') {
      await $this->fillBufferAsync(null, $timeout_ns);
    }

    // We either have a buffer, or reached EOF; either way, behavior matches
    // read, so just delegate
    return $this->read($max_bytes);
  }

  /** Read until `\n`.
   *
   * The trailing `\n` is read (so won't be returned by other calls), but is not
   * included in the return value.
   *
   * This call fails with `EPIPE` if `\n` is not seen, even if there is other
   * data.
   */
  public async function readLineAsync(): Awaitable<string> {
    $buf = $this->buffer;
    $idx = Str\search($buf, "\n");
    if ($idx !== null) {
      $this->buffer = Str\slice($buf, $idx + 1);
      return Str\slice($buf, 0, $idx);
    }

    $chunk = '';
    do {
      $chunk = await $this->handle->readAsync();
      if ($chunk === '') {
        $this->buffer = $buf;
        $this->eof = true;
        throw new OS\BrokenPipeException(
          OS\Errno::EPIPE,
          'Reached end of file before newline',
        );
      }
      $buf .= $chunk;
    } while (!Str\contains($chunk, "\n"));

    $idx = Str\search($buf, "\n");
    invariant($idx !== null, 'Should not have exited loop without newline');
    $this->buffer = Str\slice($buf, $idx + 1);
    return Str\slice($buf, 0, $idx);
  }

  // implementing interface
  public async function readFixedSizeAsync(
    int $size,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    $timer = new \HH\Lib\_Private\OptionalIncrementalTimeout(
      $timeout_ns,
      () ==> {
        _OS\throw_errno(
          OS\Errno::ETIMEDOUT,
          "Reached timeout before reading requested amount of data",
        );
      },
    );
    while (Str\length($this->buffer) < $size && !$this->eof) {
      await $this->fillBufferAsync(
        $size - Str\length($this->buffer),
        $timer->getRemainingNS(),
      );
    }
    if ($this->eof) {
      throw new OS\BrokenPipeException(
        OS\Errno::EPIPE,
        'Reached end of file before requested size',
      );
    }
    $buffer_size = Str\length($this->buffer);
    invariant(
      $buffer_size >= $size,
      "Should have read the requested data or reached EOF",
    );
    if ($size === $buffer_size) {
      $ret = $this->buffer;
      $this->buffer = '';
      return $ret;
    }
    $ret = Str\slice($this->buffer, 0, $size);
    $this->buffer = Str\slice($this->buffer, $size);
    return $ret;
  }

  /** Read a single byte from the handle.
   *
   * Fails with EPIPE if the handle is closed or otherwise unreadable.
   */
  public async function readByteAsync(?int $timeout_ns = null): Awaitable<string> {
    if ($timeout_ns is int && $timeout_ns <= 0) {
      _OS\throw_errno(OS\Errno::EINVAL, 'Timeout must be null or > 0');
    }
    if ($this->buffer === '' && !$this->eof) {
      await $this->fillBufferAsync(null, $timeout_ns);
    }
    if ($this->buffer === '') {
      _OS\throw_errno(OS\Errno::EPIPE, "Reached EOF without any more data");
    }
    $ret = $this->buffer[0];
    if ($ret == $this->buffer) {
      $this->buffer = '';
      $this->eof = true;
      return $ret;
    }
    $this->buffer = Str\slice($this->buffer, 1);
    return $ret;
  }

  public function isEndOfFile(): bool {
    return $this->eof;
  }

  private async function fillBufferAsync(
    ?int $desired_bytes,
    ?int $timeout_ns,
  ): Awaitable<void> {
    $chunk = await $this->getHandle()->readAsync($desired_bytes, $timeout_ns);
    if ($chunk === '') {
      $this->eof = true;
    }
    $this->buffer .= $chunk;
  }
}
