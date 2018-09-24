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

function fopen(string $path, string $mode): FileHandle {
  // fopen indicates errors by returning false and raising a warning; log
  // the warning and convert to an exception
  using $errors = new PHPErrorLogger(/* suppress = */ true);
  $f = \fopen($path, $mode);
  if ($f === false) {
    throw new Filesystem\FileOpenException(
      'Failed to open file: '.$errors->getLastErrorx()['message'],
    );
  }
  return new FileHandle($path, $f);
}
