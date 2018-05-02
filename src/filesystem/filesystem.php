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

/**
 * Open a file in the given mode, returning a File object.
 */
<<__ReturnDisposable, __RxLocal>>
function open_file(string $filename, FileMode $mode): File {
  return new File($filename, $mode);
}

/**
 * Remove a file
 */
function remove_file(string $filename): void {
  if (!\file_exists($filename)) {
    return;
  }

  invariant(\unlink($filename), 'Unable to remove %s', $filename);
}
