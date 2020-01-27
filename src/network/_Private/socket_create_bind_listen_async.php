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

use namespace HH\Lib\Experimental\Network;
use type HH\Lib\Experimental\OS\_Private\Errno;
use type HH\Lib\_Private\PHPWarningSuppressor;

async function socket_create_bind_listen_async(
  int $domain,
  int $type,
  int $proto,
  string $host,
  int $port,
  ?(function(resource): void) $pre_bind_callback = null,
): Awaitable<resource> {
  using new PHPWarningSuppressor();
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  $sock = \socket_create($domain, $type, $proto);
  if (!$sock is resource) {
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    $err = \socket_last_error($sock) as int;
    // using POSIX function naming instead of PHP
    throw_socket_error($err, "socket() failed");
  }
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  \socket_set_blocking($sock, false);
  if ($pre_bind_callback) {
    $pre_bind_callback($sock);
  }
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  if (!\socket_bind($sock, $host, $port)) {
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    $err = \socket_last_error($sock) as int;
    if ($err !== Errno::EINPROGRESS) {
      throw_socket_error($err, 'bind() failed');
    }
  }
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  $err = \socket_last_error($sock) as int;
  if ($err === Errno::EINPROGRESS) {
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    await \stream_await($sock, \STREAM_AWAIT_READ_WRITE);
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $err = \socket_get_option($sock, \SOL_SOCKET, \SO_ERROR);
  }
  maybe_throw_socket_error($err, 'non-blocking bind() failed asynchronously');
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  if (!\socket_listen($sock)) {
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    $err = \socket_last_error($sock) as int;
    throw_socket_error($err, 'listen() failed');
  }

  return $sock;
}
