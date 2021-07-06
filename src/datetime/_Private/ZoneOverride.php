<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\_Private\_DateTime;
use type HH\Lib\Experimental\DateTime\Zone;

/**
 * Temporary implementation detail, while we depend on builtin functions that
 * use global state for timezones.
 *
 * TODO: Make all builtin functions accept explicit timezones, then kill this.
 */
final class ZoneOverride implements \IDisposable {

  private ?string $original;

  public function __construct(?Zone $tz) {
    if ($tz is null) {
      return;
    }
    $this->original = \date_default_timezone_get();
    \date_default_timezone_set($tz as string);
  }

  public function __dispose(): void {
    if ($this->original is null) {
      return;
    }
    \date_default_timezone_set($this->original);
  }
}
