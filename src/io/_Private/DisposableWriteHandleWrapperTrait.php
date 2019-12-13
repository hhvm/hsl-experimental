<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO\_Private;

use namespace HH\Lib\{Experimental\Fileystem, Experimental\IO, Str};

trait DisposableWriteHandleWrapperTrait<T as IO\CloseableWriteHandle>
  implements IO\DisposableWriteHandle {
  require extends DisposableHandleWrapper<T>;
  require implements \IAsyncDisposable;

  final public function rawWriteBlocking(string $bytes): int {
    return $this->impl->rawWriteBlocking($bytes);
  }

  final public async function writeAsync(
    string $bytes,
    ?float $timeout_seconds = null,
  ): Awaitable<void> {
    await $this->impl->writeAsync($bytes, $timeout_seconds);
  }

  final public async function flushAsync(): Awaitable<void> {
    await $this->impl->flushAsync();
  }
}
