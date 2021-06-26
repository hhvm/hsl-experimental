<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Env\_Private;

use namespace HH;

final abstract class GlobalEnvironment {
  public static function getAll(): dict<string, string> {
    $env = (HH\global_key_exists('_ENV') ? HH\global_get('_ENV') : dict[])
      |> $$ as HH\KeyedTraversable<_, _>;

    $result = dict[];
    foreach ($env as $key => $value) {
      if ($key is string && $value is string) {
        $result[$key] = $value;
      }
    }

    return $result;
  }

  public static function setAll(dict<string, string> $env): void {
    HH\global_set('_ENV', $env);
  }
}
