<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\OS;

use namespace HH\Lib\_Private\_OS;

function socket(
  SocketDomain $domain,
  SocketType $type,
  int $protocol,
): FileDescriptor {
  return _OS\wrap_impl(
    () ==> _OS\socket($domain as int, $type as int, $protocol),
  );
}
