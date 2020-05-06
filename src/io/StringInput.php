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

/** Read from a string as if it were a file.
 *
 * This class is intended for use in unit tests.
 *
 * @see `IO\StringOutput` for writing
 * @see `IO\pipe_nd()` for more complicated tests
 */
final class StringInput implements ReadHandle, SeekableHandle {
  use ReadHandleConvenienceMethodsTrait;

  private int $offset = 0;
  private int $length;

  public function __construct(private string $buffer) {
    $this->length = Str\length($buffer);
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
    if ($this->offset >= $this->length) {
      return '';
    }
    $to_read = Math\minva($max_bytes, $this->length - $this->offset);

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
}
