<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File;
use namespace HH\Lib\_Private\_File;

<<__ReturnDisposable>>
function temporary_file(): DisposableReadWriteHandle {
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  $path = \sys_get_temp_dir().'/'.\bin2hex(\random_bytes(8));
  return new _File\TemporaryFile(
    open_read_write_nd($path, WriteMode::MUST_CREATE),
  );
}
