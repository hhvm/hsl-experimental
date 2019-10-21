<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network\_Private;

function maybe_throw_socket_error(string $operation, int $errno): void {
  if ($errno === 0) {
    return;
  }
  throw_socket_error($operation, $errno);
}
