<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File\_Private;

use namespace HH\Lib\Experimental\{File, IO};

final class DisposableFileReadWriteHandle
  extends DisposableFileHandle<File\CloseableReadWriteHandle>
  implements File\DisposableReadWriteHandle {
  use IO\_Private\DisposableReadHandleWrapperTrait<File\CloseableReadWriteHandle>;
  use IO\_Private\DisposableWriteHandleWrapperTrait<File\CloseableReadWriteHandle>;
}
