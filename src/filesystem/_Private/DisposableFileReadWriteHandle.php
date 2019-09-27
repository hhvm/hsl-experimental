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

use namespace HH\Lib\Experimental\Filesystem;

final class DisposableFileReadWriteHandle
  extends DisposableFileHandle<Filesystem\NonDisposableFileReadWriteHandle>
  implements Filesystem\DisposableFileReadWriteHandle {
  use DisposableReadHandleWrapperTrait<
    Filesystem\NonDisposableFileReadWriteHandle,
  >;
  use DisposableWriteHandleWrapperTrait<
    Filesystem\NonDisposableFileReadWriteHandle,
  >;
}
