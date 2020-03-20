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

use namespace HH\Lib\_Private\_OS;

function get_peer_name(resource $sock): (string, int) {
  $addr = '';
  $port = -1;
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  \socket_clear_error($sock);
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  $success = \socket_getpeername($sock, inout $addr, inout $port);
  if ($success) {
    return tuple($addr, $port);
  }
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  $err = \socket_last_error($sock) as int;
  throw_socket_error(
    $err === 0 ? _OS\Errno::EAFNOSUPPORT as int : $err,
    'retrieving peer address',
  );
}
