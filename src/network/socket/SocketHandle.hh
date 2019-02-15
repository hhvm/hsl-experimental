<?hh // strict

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\Experimental\IO;

/**
 * Contract for a reliable socket-based handle.
 */
interface SocketHandle extends Socket, IO\ReadHandle, IO\WriteHandle {
  /**
  * Get the address of the remote peer.
  */
  public function getRemoteAddress(): string;

  /**
   * Get the network port used by the remote peer (or NULL if not network port is being used).
   */
  public function getRemotePort(): ?int;

  /**
   * Place the given data in the socket's send queue.
   */
  <<__Override>>
  public function writeAsync(string $data): Awaitable<void>;

  /**
   * Get the number of bytes queued for send.
   * 
   * @return int Number of bytes in the socket's send queue.
   */
  public function getWriteQueueSize(): int;
}
