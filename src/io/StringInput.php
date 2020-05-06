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

use namespace HH\Lib\{Math, Str};

/** Read from a string as if it were a file.
 *
 * This class is intended for use in unit tests.
 *
 * @see `IO\StringOutput` for writing
 * @see `IO\pipe_nd()` for more complicated tests
 */
final class StringInput implements ReadHandle {
  use ReadHandleConvenienceMethodsTrait;

  public function __construct(private string $buffer) {
  }

  public async function readAsync(
    ?int $max_bytes = null,
    ?int $_timeout_nanos = null,
  ): Awaitable<string> {
    return $this->read($max_bytes);
  }

  public function read(?int $max_bytes = null): string {
    if ($max_bytes === null) {
      $buf = $this->buffer;
      $this->buffer = '';
      return $buf;
    }

    invariant($max_bytes > 0, '$max_bytes must be null or positive');
    $max_bytes = Math\minva($max_bytes, Str\length($this->buffer));

    $ret = Str\slice($this->buffer, 0, $max_bytes);
    $this->buffer = Str\slice($this->buffer, $max_bytes);
    return $ret;
  }
}
