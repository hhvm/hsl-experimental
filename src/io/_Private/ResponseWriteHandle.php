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

use namespace HH\Lib\{IO, OS};

final class ResponseWriteHandle implements IO\WriteHandle {
  use IO\WriteHandleConvenienceMethodsTrait;

  public function write(string $bytes): int {
    return namespace\response_write($bytes);
  }

  public async function writeAsync(
    string $bytes,
    ?int $_timeout_ns = null,
  ): Awaitable<int> {
    return $this->write($bytes);
  }
}
