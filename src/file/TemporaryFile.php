<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\File;

use namespace HH\Lib\IO;
use namespace HH\Lib\_Private\_IO;

final class TemporaryFile implements \IAsyncDisposable {
  public function __construct(private CloseableReadWriteHandle $handle) {}

  public function getHandle(): CloseableReadWriteHandle {
    return $this->handle;
  }
  public async function __disposeAsync(): Awaitable<void> {
    $f = $this->getHandle();
    await $f->closeAsync();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \unlink($f->getPath()->toString());
  }
}
