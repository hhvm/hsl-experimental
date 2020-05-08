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

use namespace HH\Lib\{Math, OS, Str};
use namespace HH\Lib\_Private\_OS;

enum MemoryHandleWriteMode: int {
  OVERWRITE = 0;
  APPEND = OS\O_APPEND;
}

/** Read from/write to an in-memory buffer.
 *
 * This class is intended for use in unit tests.
 *
 * @see `IO\pipe_nd()` for more complicated tests
 */
final class MemoryHandle implements ReadWriteHandle, SeekableHandle {
  use ReadHandleConvenienceMethodsTrait;
  use WriteHandleConvenienceMethodsTrait;

  private int $offset = 0;

  public function __construct(
    private string $buffer = '',
    private MemoryHandleWriteMode $writeMode = MemoryHandleWriteMode::OVERWRITE,
  ) {
  }

  public async function readAsync(
    ?int $max_bytes = null,
    ?int $_timeout_nanos = null,
  ): Awaitable<string> {
    return $this->read($max_bytes);
  }

  public function read(?int $max_bytes = null): string {
    $max_bytes ??= Math\INT64_MAX;
    invariant($max_bytes > 0, '$max_bytes must be null or positive');
    $len = Str\length($this->buffer);
    if ($this->offset >= $len) {
      return '';
    }
    $to_read = Math\minva($max_bytes, $len - $this->offset);

    $ret = Str\slice($this->buffer, $this->offset, $to_read);
    $this->offset += $to_read;
    return $ret;
  }

  public async function seekAsync(int $pos): Awaitable<void> {
    if ($pos < 0) {
      _OS\throw_errno(OS\Errno::ERANGE, "Position must be >= 0");
    }
    // Past end of file is explicitly fine
    $this->offset = $pos;
  }

  public function tell(): int {
    return $this->offset;
  }

  public function write(string $data): int {
    $length = Str\length($this->buffer);
    if ($length < $this->offset) {
      $this->buffer .= Str\repeat("\0", $this->offset - $length);
      $length = $this->offset;
    }

    if ($this->writeMode === MemoryHandleWriteMode::APPEND) {
      $this->buffer .= $data;
      $this->offset = Str\length($this->buffer);
      return Str\length($data);
    }

    invariant(
      $this->writeMode === MemoryHandleWriteMode::OVERWRITE,
      "Write mode must be OVERWRITE or APPEND",
    );

    $data_length = Str\length($data);
    $new = Str\slice($this->buffer, 0, $this->offset).$data;
    if ($this->offset < $length) {
      $new .= Str\slice($this->buffer, $this->offset + $data_length);
    }
    $this->buffer = $new;
    $this->offset += $data_length;
    return $data_length;
  }

  public async function writeAsync(
    string $data,
    ?int $timeout_nanos = null,
  ): Awaitable<int> {
    return $this->write($data);
  }

  public function getBuffer(): string {
    return $this->buffer;
  }

  /** Set the internal buffer and reset position to the beginning of the file.
   *
   * If you wish to preserve the position, use `tell()` and `seekAsync()`,
   * or `appendToBuffer()`.
   */
  public function reset(string $data = ''): void {
    $this->buffer = $data;
    $this->offset = 0;
  }

  /** Append data to the internal buffer, preserving position.
   *
   * @see `write()` if you want the offset to be changed.
   */
  public function appendToBuffer(string $data): void {
    $this->buffer .= $data;
  }
}
