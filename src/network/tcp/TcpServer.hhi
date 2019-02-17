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

use type Throwable;

/**
 * TCP socket server.
 */
final class TcpServer implements Server {
  /**
   * Servers are created using listen().
   */
  private function __construct();

  /**
   * Create a TCP server listening on the given interface and port.
   */
  public static function listen(string $host, int $port): TcpServer;

  /**
   * {@inheritdoc}
   */
  public function close(): void;

  /**
   * {@inheritdoc}
   */
  public function getAddress(): string;

  /**
   * {@inheritdoc}
   */
  public function getPort(): ?int;

  /**
   * Enable / disable simultaneous asynchronous accept requests that are queued by the operating system
   * when listening for new TCP connections.
   */
  public function setSimultaneousAccept(bool $simultaneous_accepts): void;

  /**
   * {@inheritdoc}
   */
  public function accept(): SocketHandle;
}
