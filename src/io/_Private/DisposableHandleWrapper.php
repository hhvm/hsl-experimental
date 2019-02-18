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

<<__Sealed(DisposableFileHandle::class)>>
class DisposableHandleWrapper<T as IO\DuplexHandle>
  implements IO\DisposableDuplexHandle {

  public function __construct(protected T $impl) {
  }

  public async function __disposeAsync(): Awaitable<void> {
    await $this->impl->closeAsync();
  }

  ///// IO\Handle /////

  public function isEndOfFile(): bool {
    return $this->impl->isEndOfFile();
  }

  public async function closeAsync(): Awaitable<void> {
    await $this->impl->closeAsync();
  }

  ///// IO\ReadHandle /////

  public function rawReadBlocking(?int $max_bytes = null): string {
    return $this->impl->rawReadBlocking($max_bytes);
  }

  public async function readAsync(?int $max_bytes = null): Awaitable<string> {
    return await $this->impl->readAsync($max_bytes);
  }

  public async function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    return await $this->impl->readLineAsync($max_bytes);
  }

  ///// IO\WriteHandle /////

  public function rawWriteBlocking(string $bytes): int {
    return $this->impl->rawWriteBlocking($bytes);
  }

  public async function writeAsync(string $bytes): Awaitable<void> {
    await $this->impl->writeAsync($bytes);
  }

  public async function flushAsync(): Awaitable<void> {
    await $this->impl->flushAsync();
  }
}
