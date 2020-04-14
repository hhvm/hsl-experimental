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

use namespace HH\Lib\OS;
use type HH\Lib\_Private\PHPWarningSuppressor;

async function socket_accept_async(resource $server): Awaitable<resource> {
  using new PHPWarningSuppressor();

  $retry = true;
  while (true) {
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $sock = \socket_accept($server);
    if ($sock is resource) {
      return $sock;
    }
    invariant(
      $sock === false,
      'socket_accept() returned neither `false` nor a `resource`',
    );
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $err = \socket_last_error($server) as int;
    if ($retry === false || ($err !== 0 && $err !== OS\Errno::EAGAIN)) {
      throw_socket_error($err, "accept() failed");
    }
    // accept (3P) defines select() as indicating the FD ready for read when there's a connection
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    await \stream_await($server, \STREAM_AWAIT_READ);
    $retry = false;
  }
}
