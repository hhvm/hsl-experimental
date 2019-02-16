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

use namespace HH\Lib\Experimental\IO;
use type Throwable;

/**
 * TCP socket connection.
 */
final class TcpSocket implements SocketHandle {
  /**
   * Sockets are created using connect() or TcpServer::accept().
   */
  private function __construct();

  /**
   * Connect to the given peer (will automatically perform a DNS lookup for host names).
   */
  public static function connect(
    string $host,
    int $port
  ): Awaitable<TcpSocket>;

  /**
   * Returns a pair of connected TCP sockets.
   */
  public static function pair(): (TcpSocket, TcpSocket);

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
   * Sets the TCP keep-alive timeout in seconds, 0 to disable keep-alive.
   */
  public function setKeepAlive(int $seconds): void;

  /**
   * Disables Nagle's Algorithm when set.
   */
  public function setNoDely(bool $nodely): void;

  /**
   * {@inheritdoc}
   */
  public function getRemoteAddress(): string;

  /**
   * {@inheritdoc}
   */
  public function getRemotePort(): ?int;

  /**
   * {@inheritdoc}
   */
  public function getReadHandle(): IO\ReadHandle;

  /**
   * {@inheritdoc}
   */
  public function getWriteQueueSize(): int;

  /**
   * {@inheritdoc}
   */
  public function getWriteHandle(): IO\WriteHandle;

  /**
   * {@inheritdoc}
   */
  public function rawReadBlocking(?int $max_bytes = null): string;

  /**
   * {@inheritdoc}
   */
  public async function readAsync(?int $max_bytes = null): Awaitable<string>;

  /**
   * {@inheritdoc}
   */
  public async function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string>;

  /**
   * {@inheritdoc}
   */
  public function rawWriteBlocking(string $bytes): int;

  /**
   * {@inheritdoc}
   */
  public function writeAsync(string $bytes): Awaitable<void>;

  /**
   * {@inheritdoc}
   */
  public function flushAsync(): Awaitable<void>;

  /**
   * {@inheritdoc}
   */
  public function isEndOfFile(): bool;

  /**
   * {@inheritdoc}
   */
  public async function closeAsync(): Awaitable<void>;
}
