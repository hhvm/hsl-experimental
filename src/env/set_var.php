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
 * Sets the environment variable $key to the value $value for the currently running process.
 *
 * @throws If $key is empty, contains an ASCII equals sign `=`,
 *  the NUL character `\0`, or when the $value contains
 *  the NUL character.
 */
function set_var(string $key, string $value): void {
    invariant(
        !Str\is_empty($key) &&
            !Str\contains($key, '=') &&
            !Str\contains($key, "\0"),
        'Invalid environment variable key provided.',
    );

    invariant(
        !Str\contains($value, "\0"),
        'Invalid environment variable value provided.',
    );

    $env = _Private\GlobalEnvironment::getAll();
    $env[$key] = $value;

    _Private\GlobalEnvironment::setAll($env);
}
