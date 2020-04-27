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

use namespace HH\Lib\{IO, Math, OS, Str};
use namespace HH\Lib\_Private\_OS;

trait FileDescriptorWriteHandleTrait implements IO\WriteHandle {
  require extends FileDescriptorHandle;

  final public function write(string $bytes): int {
    return OS\write($this->impl, $bytes);
  }

  final public async function writeAsync(
    string $bytes,
    ?int $timeout_ns = null,
  ): Awaitable<int> {
    if ($timeout_ns is int && $timeout_ns <= 0) {
      throw new \InvalidArgumentException('$timeout_ns must be null, or >= 0');
    }
    $timeout_ns ??= 0;

    return await $this->queuedAsync(async () ==> {
      try {
        return $this->write($bytes);
      } catch (OS\BlockingIOException $_) {
        // We need to wait, which we do below...
      }
      await $this->selectAsync(\STREAM_AWAIT_WRITE, $timeout_ns);
      return $this->write($bytes);
    });
  }

  final public function flushAsync(): Awaitable<void> {
    // no write buffer, so just wait for end of queue.
    return $this->queuedAsync(async () ==> {});
  }
}
