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

trait DisposableReadHandleWrapperTrait<T as IO\CloseableReadHandle>
  implements IO\DisposableReadHandle {
  require implements \IAsyncDisposable;
  require extends DisposableHandleWrapper<T>;

  final public function read(?int $max_bytes = null): string {
    return $this->impl->read($max_bytes);
  }

  final public async function readAsync(
    ?int $max_bytes = null,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    return await $this->impl->readAsync($max_bytes, $timeout_ns);
  }

  final public async function readAllAsync(
    ?int $max_bytes = null,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    return await $this->impl->readAllAsync($max_bytes, $timeout_ns);
  }

  final public async function readFixedSizeAsync(
    int $size,
    ?int $timeout_ns = null,
  ): Awaitable<string> {
    return await $this->impl->readFixedSizeAsync($size, $timeout_ns);
  }
}
