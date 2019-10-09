<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\{Experimental\Fileystem, Experimental\IO, Str};

abstract class DisposableHandleWrapper<T as IO\NonDisposableHandle>
  implements IO\Handle, \IAsyncDisposable {
  protected function __construct(protected T $impl) {
  }

  public async function __disposeAsync(): Awaitable<void> {
    await $this->impl->closeAsync();
  }

  final public function isEndOfFile(): bool {
    return $this->impl->isEndOfFile();
  }
}
