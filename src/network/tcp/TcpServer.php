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

use namespace HH\Lib\_Private;
use type Throwable;

/**
 * TCP socket server.
 */
final class TcpServer implements Server {
  /**
   * Servers are created using listen().
   */
  private function __construct(private resource $socket) {}

  /**
   * Create a TCP server listening on the given interface and port.
   */
  public static async function listen(
    IPAddress $ip,
    Port $port,
  ): Awaitable<TcpServer> {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $socket = self::safe(
      () ==> @\socket_create(
        is_ipv6($ip) ? \AF_INET6 : \AF_INET,
        \SOCK_STREAM,
        \SOL_TCP,
      ),
    );
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    self::safe(() ==> @\socket_bind($socket, $ip, $port));
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    self::safe(() ==> @\socket_listen($socket));
    self::safe(() ==> \socket_set_blocking($socket, false));

    return new TcpServer($socket);
  }

  /**
   * {@inheritdoc}
   */
  public async function closeAsync(): Awaitable<void> {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    self::safe(() ==> @\socket_close($this->socket));
  }

  /**
   * {@inheritdoc}
   */
  public function getAddress(): Host {
    $address = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    self::safe(() ==> @\socket_getsockname($this->socket, &$address));
    return $address;
  }

  /**
   * {@inheritdoc}
   */
  public function getPort(): ?Port {
    $address = '';
    $port = null;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    self::safe(() ==> @\socket_getsockname($this->socket, &$address, &$port));
    return $port;
  }

  /**
   * {@inheritdoc}
   */
  public function isEndOfFile(): bool {
    $buf = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $bytes =
      self::safe(() ==> @\socket_recv($this->socket, &$buf, -1, \MSG_PEEK));
    return $bytes === 0 && null === $buf;
  }

  /**
   * {@inheritdoc}
   */
  public async function accept(): Awaitable<SocketHandle> {
    $connection = @\socket_accept($this->socket);
    if ($connection !== false) {
      return new _Private\NativeSocketHandle($connection);
    }
    await \HH\Asio\usleep(10);
    return await $this->accept();
  }

  /**
   * Get socket type.
   */
  public function getType(): SocketType {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $ret = self::safe(
      () ==> @\socket_get_option($this->socket, \SOL_SOCKET, \SO_TYPE),
    );
    return SocketType::assert($ret);
  }

  private static function safe<T>((function(): T) $call): T {
    $ret = $call();
    if ($ret === false) {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $error = \socket_last_error();
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      throw new SocketException(\socket_strerror($error), $error);
    }
    return $ret;
  }
}
