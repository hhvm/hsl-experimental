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

final class TemporaryFile
  extends DisposableFileHandle<File\CloseableReadWriteHandle>
  implements File\DisposableReadWriteHandle {
  use IO\_Private\DisposableReadHandleWrapperTrait<File\CloseableReadWriteHandle>;
  use IO\_Private\DisposableWriteHandleWrapperTrait<File\CloseableReadWriteHandle>;

  public async function __disposeAsync(): Awaitable<void> {
    await parent::__disposeAsync();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \unlink($this->getPath()->toString());
  }
}
