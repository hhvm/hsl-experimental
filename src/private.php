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

function make_temporary_file(): string {
  $temp_dir = \sys_get_temp_dir();
  invariant($temp_dir, 'Unable to determine system temporary directory');
  $file = \tempnam($temp_dir, \get_current_user());
  invariant($file !== false, 'Failed to create temporary file');
  return $file;
}
