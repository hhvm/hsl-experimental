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
 * Parses input according to platform conventions for the PATH environment variable.
 */
function split_paths(string $path)[]: vec<string> {
    return Str\split($path, \PATH_SEPARATOR);
}
