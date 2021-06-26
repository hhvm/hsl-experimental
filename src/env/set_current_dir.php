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
 * Changes the current working directory to the specified path.
 */
function set_current_dir(string $directory): void {
    invariant(\chdir($directory), 'Unable to change directory');
}
