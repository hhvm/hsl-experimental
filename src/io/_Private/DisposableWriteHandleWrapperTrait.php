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

use namespace HH\Lib\{Fileystem, IO, Str};

trait DisposableWriteHandleWrapperTrait<T as IO\CloseableWriteHandle>
  implements IO\DisposableWriteHandle {
  require extends DisposableHandleWrapper<T>;
  require implements \IAsyncDisposable;

  final public function write(string $bytes): int {
    return $this->impl->write($bytes);
  }

  final public async function writeAsync(
    string $bytes,
    ?int $timeout_ns = null,
  ): Awaitable<int> {
    return await $this->impl->writeAsync($bytes, $timeout_ns);
  }

  final public async function flushAsync(): Awaitable<void> {
    await $this->impl->flushAsync();
  }

  final public async function writeAllAsync(
    string $bytes,
    ?int $timeout_ns = null,
  ): Awaitable<void> {
    return await $this->impl->writeAllAsync($bytes, $timeout_ns);
  }
}
