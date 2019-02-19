<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */
 
namespace HH\Lib\Experimental\Network\_Private;

use namespace HH\Lib\Experimental\Network;

function dns_lookup(string $host, string $type = 'A'): Network\IPAddress {
  try {
    return Network\ipv4($host);
  } catch (\HH\InvariantException $e) {}
  try {
    return Network\ipv6($host);
  } catch (\HH\InvariantException $e) {}

  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  $records = \dns_get_record($host);
  foreach ($records as $record) {
    if ($record['type'] === 'A') {
      return Network\ipv4($record['ip']);
    } elseif ($record['type'] === 'AAAA') {
      return Network\ipv6($record['ipv6']);
    }
  }
  \invariant_violation('Invalid host');
}
