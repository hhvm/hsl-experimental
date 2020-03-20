<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_Network;

function maybe_throw_socket_error(int $php_socket_error, string $message): void {
  if ($php_socket_error === 0) {
    return;
  }
  throw_socket_error($php_socket_error, $message);
}
