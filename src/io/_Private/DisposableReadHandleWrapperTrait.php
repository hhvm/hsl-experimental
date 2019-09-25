<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\{Experimental\Filesystem, Experimental\IO, Str};

trait DisposableReadHandleWrapperTrait<T as IO\NonDisposableReadHandle>
  implements IO\DisposableReadHandle {
  require implements \IAsyncDisposable;
  require extends DisposableHandleWrapper<T>;

  final public function rawReadBlocking(?int $max_bytes = null): string {
    return $this->impl->rawReadBlocking($max_bytes);
  }

  final public async function readAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    return await $this->impl->readAsync($max_bytes);
  }

  final public async function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    return await $this->impl->readLineAsync($max_bytes);
  }
}
