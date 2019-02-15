namespace HH\Lib\Experimental\Network;

use type HH\Lib\Experimental\IO\ReadHandle;
use type HH\Lib\Experimental\IO\WriteHandle;

/**
 * Contract for a reliable socket-based handle.
 */
interface SocketHandle extends Socket, ReadHandle, WriteHandle {
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
   * 
   * Implementations may try an immediate write before placeing data in the send queue.
   * 
   * @param string $data Data to be sent.
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
