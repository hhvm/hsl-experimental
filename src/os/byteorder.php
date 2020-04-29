<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\OS {

  newtype NetShort = int;
  newtype NetLong = int;

  use namespace HH\Lib\_Private\_OS;

  /** Convert a 16-bit positive integer from host byte order to network byte
   * order.
   *
   * See `man 3 byteorder`.
   *
   * @see ntohs for the inverse
   * @see htonl for 32-bits
   */
  function htons(int $in): NetShort {
    if ($in === 0) {
      return 0;
    }

    if ($in < 0 || $in >= (1 << 16)) {
      _OS\throw_errno(
        Errno::ERANGE,
        'Input must fit in a 16-bit unsigned integer',
      );
    }
    // hackfmt-ignore
    return
      (($in & 0x00ff) << 8) |
      (($in & 0xff00) >> 8);
  }

  /** Convert a 16-bit positive integer from network byte order to host byte
   *order.
   *
   * See `man 3 byteorder`
   *
   * @see htons for the inverse
   * @see ntohl for 32-bits
   */
  function ntohs(NetShort $in): int {
    // We're just swapping :)
    return htons($in);
  }

  /** Convert a 32-bit positive integer from host byte order to network byte
   * order.
   *
   * See `man 3 byteorder`.
   *
   * @see ntohl for the inverse
   * @see htons for 16-bits
   */

  function htonl(int $in): NetLong {
    if ($in === 0) {
      return 0;
    }

    if ($in < 0 || $in >= (1 << 32)) {
      _OS\throw_errno(
        Errno::ERANGE,
        'Input must fit in a 32-bit unsigned integer',
      );
    }
    // hackfmt-ignore
    return
      (($in & 0x000000ff) << 24) |
      (($in & 0x0000ff00) << 8) |
      (($in & 0x00ff0000) >> 8) |
      (($in & 0xff000000) >> 24);
  }

  /** Convert a 32-bit positive integer from network byte order to host byte
   *order.
   *
   * See `man 3 byteorder`
   *
   * @see htonl for the inverse
   * @see ntohs for 16-bits
   */

  function ntohl(NetLong $in): int {
    return htonl($in);
  }
}

namespace HH\Lib\_Private\_OS {

  use namespace HH\Lib\OS;

  function int_as_netshort_UNSAFE(int $in): OS\NetShort {
    return $in;
  }

  function int_as_netlong_UNSAFE(int $in): OS\NetLong {
    return $in;
  }

  <<__Memoize>>
  function native_uses_network_byte_order(): bool {
    // Until ~ HHVM 4.55, sockaddr members are implicitly converted to
    // host byte order. Undo that if needed.
    //
    // The diff adding INETADDR_ANY is the next commit after the byte order
    // change commit.
    return \defined("HH\\Lib\\_Private\\_OS\\INETADDR_ANY");
  }

  /** Temporary migration hack */
  function native_to_netshort_FIXME(int $in): OS\NetShort {
    if (native_uses_network_byte_order()) {
      return $in;
    }
    return OS\htons($in);
  }

  /** Temporary migration hack */
  function native_to_netlong_FIXME(int $in): OS\NetLong {
    if (native_uses_network_byte_order()) {
      return $in;
    }
    return OS\htonl($in);
  }

  /** Temporary migration hack */
  function netshort_to_native_FIXME(OS\NetShort $in): int {
    if (native_uses_network_byte_order()) {
      return $in;
    }
    return OS\ntohs($in);
  }

  /** Temporary migration hack */
  function netlong_to_native_FIXME(OS\NetLong $in): int {
    if (native_uses_network_byte_order()) {
      return $in;
    }
    return OS\ntohl($in);
  }
}
