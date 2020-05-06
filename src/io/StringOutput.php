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

use namespace HH\Lib\Str;

/** Write to a string as if it were a file.
 *
 * This class is intended for unit testing.
 *
 * @see `IO\StringInput` for reading
 * @see `IO\pipe_nd()` for more complicated cases
 */
final class StringOutput implements WriteHandle {
  use WriteHandleConvenienceMethodsTrait;

  private string $buffer = '';

  public function write(string $data): int {
    $this->buffer .= $data;
    return Str\length($data);
  }

  public async function writeAsync(
    string $data,
    ?int $_timeout_nanos = null,
  ): Awaitable<int> {
    return $this->write($data);
  }

  public async function flushAsync(): Awaitable<void> {
  }

  public function getBuffer(): string {
    return $this->buffer;
  }

  public function clearBuffer(): void {
    $this->buffer = '';
  }
}
