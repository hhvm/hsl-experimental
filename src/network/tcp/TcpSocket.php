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

use namespace HH\Lib\{Experimental\IO, C, Str};
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
    string $host,
    int $port,
  ): Awaitable<TcpSocket> {
    $ip = _Private\dns_lookup($host);
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $socket = \socket_create(
      is_ipv4($ip) ? (int) SocketDomain::INET : (int)SocketDomain::INET6,
      (int)SocketType::STREAM,
      (int)SocketProtocol::TCP,
    );
    if ($socket === false) {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $error = \socket_last_error();
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      throw new SocketException(\socket_strerror($error), $error);
    }
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $ret = \socket_connect($socket, (string)$ip, $port);
    if ($ret === false) {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $error = \socket_last_error();
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      @\socket_close($socket);
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      throw new SocketException(\socket_strerror($error), $error);
    }
    return new TcpSocket($socket);
  }

  /**
   * Sets the TCP keep-alive timeout in seconds, 0 to disable keep-alive.
   */
  public function setKeepAlive(int $seconds): void {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \socket_set_option(
      $this->socket,
      (int)SocketProtocol::TCP,
      \SO_KEEPALIVE,
      $seconds,
    );
    if ($result === false) {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $error = \socket_last_error();
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      throw new SocketException(\socket_strerror($error), $error);
    }
  }

  /**
   * Disables Nagle's Algorithm when set.
   */
  public function setNoDely(bool $nodely): void {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \socket_set_option(
      $this->socket,
      (int)SocketProtocol::TCP,
      \TCP_NODELAY,
      $nodely ? 1 : 0,
    );
    if ($result === false) {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $error = \socket_last_error();
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      throw new SocketException(\socket_strerror($error), $error);
    }
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
