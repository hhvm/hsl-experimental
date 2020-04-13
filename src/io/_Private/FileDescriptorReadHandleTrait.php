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

  final public function read(?int $max_bytes = null): string {
    $max_bytes ??= DEFAULT_READ_BUFFER_SIZE;

    if ($max_bytes <= 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    return OS\read($this->impl, $max_bytes);
  }

  final public async function readAsync(
    ?int $max_bytes = null,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    $max_bytes ??= DEFAULT_READ_BUFFER_SIZE;
    $timeout_ns ??= 0;

    if ($max_bytes <= 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or > 0');
    }
    if ($timeout_ns < 0) {
      throw new \InvalidArgumentException('$timeout_ns must be null, or >= 0');
    }

    try {
      $chunk = $this->read($max_bytes);
      return $chunk;
    } catch (OS\BlockingIOException $e) {
    }

    await $this->selectAsync(\STREAM_AWAIT_READ, $timeout_ns);
    return $this->read($max_bytes);
  }
}
