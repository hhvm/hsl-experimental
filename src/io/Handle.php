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

use namespace HH\Lib\Experimental\{File, Network};

/** An interface for IO handles.
 *
 * Order of operations is guaranteed.
 */
<<__Sealed(
  File\Handle::class,
  Network\Socket::class,
  NonDisposableHandle::class,
  ReadHandle::class,
  UserspaceHandle::class,
  SeekableHandle::class,
  WriteHandle::class,
  _Private\DisposableHandleWrapper::class,
)>>
interface Handle {
  public function isEndOfFile(): bool;
}
