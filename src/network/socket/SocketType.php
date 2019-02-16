<?hh // strict

namespace HH\Lib\Experimental\Network;

enum SocketType: int {
  /**
   * Provides sequenced, reliable, full-duplex, connection-based byte streams.
   * An out-of-band data transmission mechanism may be supported.
   * The TCP protocol is based on this socket type.
   */
  STREAM = \SOCK_STREAM;

  /**
   * Supports datagrams (connectionless, unreliable messages of a fixed maximum length).
   * The UDP protocol is based on this socket type.
   */
  DATAGRAMS = \SOCK_DGRAM;

  /**
   * Provides a sequenced, reliable, two-way connection-based data transmission path for datagrams
   * of fixed maximum length; a consumer is required to read an entire packet with each read call.
   */
  SEQUENCED = \SOCK_SEQPACKET;

  /**
   * Provides raw network protocol access.
   * This special type of socket can be used to manually construct any type of protocol.
   * A common use for this socket type is to perform ICMP requests (like ping)
   */
  RAW = \SOCK_RAW;

  /**
   * Provides a reliable datagram layer that does not guarantee ordering.
   * This is most likely not implemented on your operating system.
   */
  RDM = \SOCK_RDM;
}
