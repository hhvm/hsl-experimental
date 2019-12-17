<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

/**
 * A non-disposable handle that is explicitly closeable.
 *
 * Some handles, such as those returned by `IO\server_error()` may
 * be neither disposable nor closeable.
 */
interface CloseableHandle extends Handle {
  /** Complete pending operations then close the handle */
  public function closeAsync(): Awaitable<void>;
}
