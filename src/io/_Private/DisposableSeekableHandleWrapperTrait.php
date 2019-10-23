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

use namespace HH\Lib\Experimental\IO;

trait DisposableSeekableHandleWrapperTrait<T as IO\NonDisposableSeekableHandle>
  implements IO\DisposableSeekableHandle {
  require extends DisposableHandleWrapper<T>;
  require implements \IAsyncDisposable;

  final public function seekAsync(int $offset): Awaitable<void> {
    return $this->impl->seekAsync($offset);
  }
}
