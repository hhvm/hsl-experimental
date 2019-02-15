namespace HH\Lib\Experimental\Network;

use type Throwable;

interface Socket {
  /**
   * Close the underlying socket.
   * 
   * @param Throwable $e Reason for close, will be set as previous error.
   */
  public function close(?Throwable $e = null): void;

  /**
   * Get the local address of the socket.
   */
  public function getAddress(): string;

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?int;

  /**
   * Change the value of a socket option, options are declared as class constants.
   * 
   * @param int $option Option to be changed.
   * @param mixed $value New value to be set.
   * @return bool Will return false when the option is not supported.
   */
  public function setOption(int $option, mixed $value): bool;
}
