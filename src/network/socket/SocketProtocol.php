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

enum SocketProtocol: int {
  /**
   * The Internet Control Message Protocol is used primarily by gateways and hosts 
   * to report errors in datagram communication.
   * The "ping" command (present in most modern operating systems) is an example application of ICMP. 
   */
  ICMP = \SOL_SOCKET;

  /**
   * The User Datagram Protocol is a connectionless, unreliable, protocol with fixed record lengths.
   * Due to these aspects, UDP requires a minimum amount of protocol overhead.
   */
  UDP = \SOL_UDP;

  /*
   * The Transmission Control Protocol is a reliable, connection based, stream oriented,
   * full duplex protocol.
   * TCP guarantees that all data packets will be received in the order in which they were sent.
   * If any packet is somehow lost during communication, TCP will automatically retransmit the packet
   * until the destination host acknowledges that packet.
   * For reliability and performance reasons, the TCP implementation itself decides the appropriate
   * octet boundaries of the underlying datagram communication layer.
   * Therefore, TCP applications must allow for the possibility of partial record transmission. 
   */
  TCP = \SOL_TCP;
}
