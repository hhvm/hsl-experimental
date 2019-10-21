<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\TCP;

use namespace HH\Lib\Experimental\Network;
use type HH\Lib\Experimental\OS\Errno;
use type HH\Lib\_Private\PHPWarningSuppressor;

final class Server
  implements Network\Server<Socket, DisposableSocket, NonDisposableSocket> {
  private function __construct(private resource $impl) {
  }

  public static async function createAsync(
    Network\IPProtocolVersion $ipv,
    string $address,
    int $port,
  ): Awaitable<this> {
    switch ($ipv) {
      case Network\IPProtocolVersion::IPV6:
        $af = \AF_INET6;
        break;
      case Network\IPProtocolVersion::IPV4:
        $af = \AF_INET;
        break;
    }
    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    $sock = \socket_create($af, \SOCK_STREAM, \SOL_TCP);
    if (!$sock is resource) {
      /* HH_IGNORE_ERROR[2049] PHPStdLib */
      /* HH_IGNORE_ERROR[4107] PHPStdLib */
      $err = \socket_last_error($sock) as int;
      Network\_Private\throw_socket_error('creating socket', $err);
    }
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    \socket_set_blocking($sock, false);
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    if (!\socket_bind($sock, $address, $port)) {
      /* HH_IGNORE_ERROR[2049] PHPStdLib */
      /* HH_IGNORE_ERROR[4107] PHPStdLib */
      $err = \socket_last_error($sock) as int;
      if ($err !== Errno::EINPROGRESS) {
        Network\_Private\throw_socket_error('binding socket', $err);
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
    Network\_Private\maybe_throw_socket_error('binding socket', $err);
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    if (!\socket_listen($sock)) {
      /* HH_IGNORE_ERROR[2049] PHP stdlib */
      /* HH_IGNORE_ERROR[4107] PHP stdlib */
      $err = \socket_last_error($sock) as int;
      Network\_Private\throw_socket_error('listening on socket', $err);
    }

    return new self($sock);
  }

  <<__ReturnDisposable>>
  public async function nextConnectionAsync(): Awaitable<DisposableSocket> {
    return new _Private\DisposableTCPSocket(await $this->nextConnectionNDAsync());
  }

  public async function nextConnectionNDAsync(): Awaitable<NonDisposableSocket> {
    $retry = true;
    while (true) {
      /* HH_IGNORE_ERROR[2049] PHP stdlib */
      /* HH_IGNORE_ERROR[4107] PHP stdlib */
      $sock = \socket_accept($this->impl);
      if ($sock is resource) {
        return new _Private\NonDisposableTCPSocket($sock);
      }
      invariant(
        $sock === false,
        'socket_accept() returned neither `false` nor a `resource`',
      );
      /* HH_IGNORE_ERROR[2049] PHP stdlib */
      /* HH_IGNORE_ERROR[4107] PHP stdlib */
      $err = \socket_last_error($this->impl) as int;
      if ($retry === false || $err !== Errno::EAGAIN) {
        Network\_Private\throw_socket_error('accepting connection', $err);
      }
      // accept (3P) defines select() as indicating the FD ready for read when there's a connection
      /* HH_IGNORE_ERROR[2049] PHP stdlib */
      /* HH_IGNORE_ERROR[4107] PHP stdlib */
      await \stream_await($this->impl, \STREAM_AWAIT_READ);
      $retry = false;
    }
  }
}
