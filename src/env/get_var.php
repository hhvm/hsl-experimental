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
 * Fetches the environment variable $key from the current process.
 */
function get_var(string $key): ?string {
  invariant(
    !Str\is_empty($key) &&
      !Str\contains($key, '=') &&
      !Str\contains($key, "\0"),
    'Invalid environment variable key provided.',
  );

  $variables = get_vars();

  return $variables[$key] ?? null;
}
