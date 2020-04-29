<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\OS;

/** The type of the network form of an INET (IPv4) address.
 *
 * This type is primarily here for consistency with IPv6, where an opaque type
 * is used.
 *
 * this type is not opaque, as standard practice is to use `htonl()` to create
 * IPv4 addresses.
 *
 * @see `in6_addr`
 */
type in_addr = NetLong;
