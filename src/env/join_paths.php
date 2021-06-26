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

use HH\Lib\Str;

/**
 * Joins a collection of paths appropriately for the PATH environment variable.
 */
function join_paths(string ...$paths)[]: string {
    return Str\join($paths, \PATH_SEPARATOR);
}
