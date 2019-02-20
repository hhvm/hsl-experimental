<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\{Experimental\IO, C, Str, _Private};
use type Throwable;

/**
 * TCP socket connection.
 *
 * Sockets are created using connect() or TcpServer::accept().
 */
final class TcpSocket
  extends _Private\NativeSocketHandle
  implements SocketHandle {
  /**
   * Connect to the given peer (will automatically perform a DNS lookup for host names).
   */
  public static async function connect(
    Host $host,
    Port $port,
  ): Awaitable<TcpSocket> {
    $ip = _Private\dns_lookup($host);
    $domain = is_ipv4($ip) ? \AF_INET : \AF_INET6;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $socket =
      static::safe(() ==> @\socket_create($domain, \SOCK_STREAM, \SOL_TCP));
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(() ==> @\socket_connect($socket, $ip, $port));
    return new TcpSocket($socket);
  }

  /**
   * Sets the TCP keep-alive timeout in seconds, 0 to disable keep-alive.
   */
  public function setKeepAlive(int $seconds): void {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(
      () ==>
        @\socket_set_option($this->socket, \SOL_TCP, \SO_KEEPALIVE, $seconds),
    );
  }

  /**
   * Disables Nagle's Algorithm when set.
   */
  public function setNoDely(bool $nodely): void {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(
      () ==>
        @\socket_set_option($this->socket, \SOL_TCP, \TCP_NODELAY, $nodely),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getReadHandle(): IO\ReadHandle {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWriteHandle(): IO\WriteHandle {
    return $this;
  }
}
