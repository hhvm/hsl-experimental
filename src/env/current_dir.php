<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Env;

/**
 * Returns the current working directory.
 */
function current_dir(): string {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $directory = \getcwd();
  invariant(
    $directory is string,
    'Unable to retrieve current working directory.',
  );

  return $directory;
}
