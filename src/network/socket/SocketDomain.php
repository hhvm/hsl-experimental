<?hh // strict

namespace HH\Lib\Experimental\Network;

enum SocketDomain: int {
  /**
   * IPv4 Internet based protocols. TCP and UDP are common protocols of this protocol family.
   */
  INET = \AF_INET;

  /**
   * IPv6 Internet based protocols. TCP and UDP are common protocols of this protocol family.
   */
  INET6 = \AF_INET6;

  /**
   * Local communication protocol family.
   * High efficiency and low overhead make it a great form of IPC (Interprocess Communication).
   */
  UNIX = \AF_UNIX;
}
