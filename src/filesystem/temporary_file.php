<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Filesystem;

use namespace HH\Lib\_Private;

<<__ReturnDisposable>>
function temporary_file(): DisposableFileReadWriteHandle {
  $path = \sys_get_temp_dir().'/'.\bin2hex(\random_bytes(8));
  return new _Private\TemporaryFile(open_read_write_non_disposable(
    $path,
    FileReadWriteMode::MUST_CREATE,
  ) as _Private\FileHandle);
}
