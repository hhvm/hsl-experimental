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
