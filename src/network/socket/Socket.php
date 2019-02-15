<?hh // strict

namespace HH\Lib\Experimental\Network;

use type Throwable;

interface Socket {
  /**
   * Close the underlying socket.
   */
  public function close(): void;

  /**
   * Get the local address of the socket.
   */
  public function getAddress(): string;

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?int;
}
