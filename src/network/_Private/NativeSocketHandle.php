<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\{IO, Network};
use namespace HH\Lib\Str;

class NativeSocketHandle implements Network\SocketHandle {
  private bool $closed = false;
  public function __construct(
    protected resource $socket,
  ) {
      static::safe(() ==> @\socket_set_nonblock($socket));
    }

  private ?Awaitable<mixed> $lastOperation;
  protected function queuedAsync<T>(
    (function(): Awaitable<T>) $next,
  ): Awaitable<T> {
    $last = $this->lastOperation;
    $queue = async {
      await $last;
      return await $next();
    };
    $this->lastOperation = $queue;
    return $queue;
  }

  final public function rawReadBlocking(?int $max_bytes = null): string {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($max_bytes === 0) {
      return '';
    }
    return (string)static::safe(
      () ==> {
        $buf = '';
        /* HH_IGNORE_ERROR[2049] __PHPStdLib */
        /* HH_IGNORE_ERROR[4107] __PHPStdLib */
        @\socket_recv($this->socket, &$buf, $max_bytes ?? -1, \MSG_WAITALL);
        return $buf;
      }
    );
  }

  final public async function readAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }

    $data = '';
    while (($max_bytes === null || $max_bytes > 0) && !$this->isEndOfFile()) {
      $chunk = $this->rawReadBlocking($max_bytes);
      $data .= $chunk;
      if ($max_bytes !== null) {
        $max_bytes -= Str\length($chunk);
      }
      if ($max_bytes === null || $max_bytes > 0) {
        await \stream_await(
          $this->socket,
          \STREAM_AWAIT_READ | \STREAM_AWAIT_ERROR,
        );
      }
    }
    return $data;
  }

  final public async function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }

    if ($max_bytes === null) {
      // The PHP_NORMAL_READ parameter for socket_read will stop when it encounters \n or \r.
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $impl = () ==> @\socket_read($this->socket, -1, \PHP_NORMAL_READ);
    } else {
      // The PHP_NORMAL_READ parameter for socket_read will stop when it encounters \n or \r.
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $impl =
        () ==> @\socket_read($this->socket, $max_bytes + 1, \PHP_NORMAL_READ);
    }
    $data = $impl();
    while ($data === false && !$this->isEndOfFile()) {
      await \stream_await(
        $this->socket,
        \STREAM_AWAIT_READ | \STREAM_AWAIT_ERROR,
      );
      $data = $impl();
    }
    return $data === false ? '' : $data;
  }

  final public function rawWriteBlocking(string $bytes): int {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return static::safe(() ==> @\socket_write($this->socket, $bytes)) as int;
  }

  final public function writeAsync(string $bytes): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      while (true) {
        $written = $this->rawWriteBlocking($bytes);
        $bytes = Str\slice($bytes, $written);
        if ($bytes === '') {
          break;
        }
        await \stream_await(
          $this->socket,
          \STREAM_AWAIT_WRITE | \STREAM_AWAIT_ERROR,
        );
      }
    });
  }

  final public function flushAsync(): Awaitable<void> {
    return $this->queuedAsync(async () ==> {
      /* HH_IGNORE_ERROR[2049] */
      /* HH_IGNORE_ERROR[4107] */
      @\socket_shutdown($this->socket);
    });
  }

  final public function isEndOfFile(): bool {
    \socket_clear_error();
    if ($this->closed) {
      return true;
    }
   $buf = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $bytes = \socket_recv($this->socket, &$buf, -1, \MSG_PEEK);
    return $bytes === 0 && null === $buf;
  }

  final public async function closeAsync(): Awaitable<void> {
    await $this->flushAsync();
    $this->closed = true;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    @\socket_close($this->socket);
  }

  /**
   * Get the local address of the socket.
   */
  public function getAddress(): Network\Host {
    $address = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(() ==> @\socket_getsockname($this->socket, &$address));
    return $address;
  }

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?Network\Port {
    $address = '';
    $port = null;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(() ==> @\socket_getsockname($this->socket, &$address, &$port));
    return $port;
  }

  /**
   * Get the address of the remote peer.
   */
  public function getRemoteAddress(): Network\Host {
    $address = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(() ==> @\socket_getpeername($this->socket, &$address));
    return $address;
  }

  /**
   * Get the network port used by the remote peer (or NULL if not network port is being used).
   */
  public function getRemotePort(): ?Network\Port {
    $address = '';
    $port = null;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    static::safe(() ==> @\socket_getpeername($this->socket, &$address, &$port));
    return $port;
  }

  public function getType(): Network\SocketType {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $ret = static::safe(
      () ==> @\socket_get_option($this->socket, \SOL_SOCKET, \SO_TYPE),
    );
    return Network\SocketType::assert($ret);
  }

  protected static function safe<T>((function(): T) $call): T {
    \socket_clear_error();
    $ret = $call();
    if ($ret === false) {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $error = \socket_last_error();
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      throw new Network\SocketException(\socket_strerror($error), $error);
    }
    return $ret;
  }
}
