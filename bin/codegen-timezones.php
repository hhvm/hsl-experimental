#!/usr/bin/env hhvm
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

use namespace HH\Lib\{Keyset, Regex, Str};

/**
 * Generate the HH\Lib\Experimental\DateTime\Zone enum based on the timezones
 * supported by the current HHVM version.
 */
<<__EntryPoint>>
function codegen_timezones(): void {
  require_once(__DIR__.'/../vendor/autoload.hack');
  \Facebook\AutoloadMap\initialize();

  $argv = \HH\global_get('argv') as KeyedContainer<_, _>;
  $output_path = idx($argv, 1);
  invariant(
    $output_path is string,
    'Usage: %s <output_path>',
    $argv[0] as string,
  );

  $enum_values = dict['UTC' => 'UTC'];

  // Add all offsets that at least one supported timezone uses.
  // Example: PLUS_0100 => '+01:00';

  $jan = new \DateTime('2021-01-01');
  $jul = new \DateTime('2021-07-01');

  $offsets = keyset[];

  foreach (\DateTimeZone::listIdentifiers() as $id) {
    $tz = new \DateTimeZone($id);
    $offsets[] = $tz->getOffset($jan);
    $offsets[] = $tz->getOffset($jul);
  }

  foreach (Keyset\sort($offsets) as $s) {
    if ($s === 0) { // UTC
      continue;
    }
    $negative = $s < 0;
    if ($negative) {
      $s = -$s;
    }
    $h = (int)($s / 3600);
    $s %= 3600;
    $m = (int)($s / 60);
    $s %= 60;
    invariant(
      $s === 0,
      'Timezone offset is not a full number of minutes: %d:%d:%d',
      $h,
      $m,
      $s,
    );
    $enum_values[
      Str\format('%s_%02d%02d', $negative ? 'MINUS' : 'PLUS', $h, $m)
    ] = Str\format('%s%02d:%02d', $negative ? '-' : '+', $h, $m);
  }

  // Add all supported tzdata timezones.
  // Example: AMERICA_LOS_ANGELES = 'America/Los_Angeles';

  foreach (Keyset\sort(\DateTimeZone::listIdentifiers()) as $id) {
    if ($id === 'UTC') {
      continue;
    }
    invariant(
      Regex\matches($id, re"#^[A-Za-z]+/[A-Za-z_/\\-]+\$#"),
      'Unexpected timezone identifier format: %s',
      $id,
    );
    $enum_values[Regex\replace($id, re"#[/\\-]#", '_') |> Str\uppercase($$)] =
      $id;
  }

  // Write output file.
  $code = Str\ends_with($output_path, '.php') ? "<?hh\n" : '';
  $code .= <<<END
/**
 * This file is generated. Do not modify it manually!
 * Regenerate using: bin/codegen-timezones.php
 */

namespace HH\Lib\Experimental\DateTime;

/**
 * All supported timezones. This includes:
 *
 * - UTC
 * - all supported tzdata timezones like "America/Los_Angeles"
 * - all UTC offsets that at least one supported timezone uses for either its
 *   winter time or its summer time
 *
 * We intentionally don't include common aliases like PST (Pacific Standard
 * Time) because these can be confusing and ambiguous, e.g. the meaning of PST
 * is unclear when dealing with dates that fall in PDT (Pacific Daylight Time)
 * range. Instead, use the standard tzdata timezone like "America/Los_Angeles"
 * which correctly resolves to either PST or PDT for any specified date; or
 * you can use an explicit offset like UTC-08:00 which is always unambiguous.
 */
enum Zone : string {

END;

  foreach ($enum_values as $name => $value) {
    $code .= '  '.$name.' = '.\var_export($value, true).";\n";
  }
  $code .= "}\n";

  \file_put_contents($output_path, $code);
}
