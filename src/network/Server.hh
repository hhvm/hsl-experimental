<?hh // strict

namespace HH\Lib\Experimental\Network;

/**
 * Contract for a server that accepts reliable socket handles.
 */
interface Server extends Socket {
  /**
   * Accept the next inbound socket connection.
   */
  public function accept(): SocketHandle;
}
