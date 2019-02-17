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

function dns_lookup(string $host): string {
  if (Network\is_ipv4($host) || Network\is_ipv6($host)) {
      return Network\ip($host);
  }
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  $records = \dns_get_record($host);
  foreach ($records as $record) {
    if ($record['type'] === 'A') {
      return Network\ip($record['ip']);
    } elseif ($record['type'] === 'AAAA') {
      return Network\ip($record['ipv6']);
    }
  }
  \invariant_violation('Invalid host');
}
