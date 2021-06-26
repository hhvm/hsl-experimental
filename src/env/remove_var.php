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

use namespace HH\Lib\Str;

/**
 * Removes an environment variable from the environment of the currently running process.
 *
 * @throws If $key is empty, or contains an ASCII equals sign `=` or
 *  the NUL character `\0`.
 */
function remove_var(string $key): void {
    invariant(
        !Str\is_empty($key) &&
            !Str\contains($key, '=') &&
            !Str\contains($key, "\0"),
        'Invalid environment variable key provided.',
    );

    // TODO(azjezz): putenv($key), or unset($_ENV[$key])?
}
