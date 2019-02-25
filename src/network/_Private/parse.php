<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\{Experimental\Network, Str};


function parseSocketAddress(string $address): Network\Host {
  if (!Str\contains($address, ':')) {
    // address doesn't contain a port
    return $address;
  }

  $pos = Str\search($address, ':') as int;
  // is ipv6 ?
  if (Str\search($address, ':', $pos)) {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return Str\slice($address, 0, \strrpos($address, ':'));
  } else {
    // ipv4
    $parts = Str\split($address, ':', 2);
    return $parts[0];
  }
}

function parseSocketPort(string $address): ?Network\Port {
  if (!Str\contains($address, ':')) {
    return null;
  }

  $pos = Str\search($address, ':') as int;
  // is ipv6 ?
  if (Str\search($address, ':', $pos)) {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $port = Str\slice($address, \strrpos($address, ':') + 1);
    return (int)$port;
  } else {
    // ipv4
    $parts = Str\split($address, ':', 2);
    return (int)$parts[1];
  }
}
