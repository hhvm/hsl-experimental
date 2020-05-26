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

use namespace HH\Lib\Str;
use namespace HH\Lib\_Private\_OS;

/** Create a temporary file using a template filename, with an invariant suffix,
 * and the specified open flags.
 *
 * The template must contain exactly 6 `X` characters, followed immediately
 * by the invariant suffix.
 *
 * The length of the suffix must be specified; for example, if the template is
 * `fooXXXXXXbar`, the suffix len is 3, or for `fooXXXXXXbarbaz` it is 6. For
 * `fooXXXXXXXXXXXXXXXXXX`, any suffix len between 0 and 12 is valid.
 *
 * The template may be either a relative or absolute path, however the parent
 * directory must already exist.
 *
 * This function takes the same flags as `OS\open()`; like that function,
 * `O_CLOEXEC` is implied.
 *
 * The temporary file:
 * - will be a new file (i.e. `O_CREAT | O_EXCL`)
 * - be owned by the current user
 * - be created with mode 0600
 *
 * @see open
 * @see mkostemp
 * @see mkstemp
 * @see mkstemps
 *
 * @returns a `FileDescriptor` and the actual path.
 */
function mkostemps(
  string $template,
  int $suffix_length,
  int $flags,
): (FileDescriptor, string) {
  if ($suffix_length < 0) {
    _OS\throw_errno(Errno::EINVAL, 'Suffix length must not be negative');
  }
  if ($suffix_length === 0) {
    if (!Str\ends_with($template, 'XXXXXX')) {
      _OS\throw_errno(
        Errno::EINVAL,
        'Template must end with exactly 6 `X` characters',
      );
    }
  } else if ($suffix_length > 0) {
    $base = Str\slice($template, 0, Str\length($template) - $suffix_length);
    if (!Str\ends_with($base, 'XXXXXX')) {
      _OS\throw_errno(
        Errno::EINVAL,
        'Template must be of form prefixXXXXXXsuffix - exactly 6 `X` '.
        'characters are required',
      );
    }
  }
  // We do not want LightProcess to be observable.
  $flags |= O_CLOEXEC;

  return _OS\wrap_impl(() ==> _OS\mkostemps($template, $suffix_length, $flags));
}

/** Create a temporary file using a template filename, with an invariant suffix.
 *
 * The template must contain exactly 6 `X` characters, followed immediately
 * by the invariant suffix.
 *
 * The length of the suffix must be specified; for example, if the template is
 * `fooXXXXXXbar`, the suffix len is 3, or for `fooXXXXXXbarbaz` it is 6. For
 * `fooXXXXXXXXXXXXXXXXXX`, any suffix len between 0 and 12 is valid.
 *
 * The template may be either a relative or absolute path, however the parent
 * directory must already exist.
 *
 * The temporary file:
 * - will be a new file (i.e. `O_CREAT | O_EXCL`)
 * - be owned by the current user
 * - be created with mode 0600
 *
 * @see mkostemp
 * @see mkostemps
 * @see mkstemp
 *
 * @returns a `FileDescriptor` and the actual path.
 */
function mkstemps(string $template, int $suffix_len): (FileDescriptor, string) {
  return mkostemps($template, $suffix_len, /* flags = */ 0);
}

/** Create a temporary file using a template filename and the specified open
 * flags.
 *
 * The template must end with exactly 6 `X` characters; the template may be
 * either a relative or absolute path, however the parent directory must already
 * exist.
 *
 * This function takes the same flags as `OS\open()`; like that function,
 * `O_CLOEXEC` is implied.
 *
 * The temporary file:
 * - will be a new file (i.e. `O_CREAT | O_EXCL`)
 * - be owned by the current user
 * - be created with mode 0600
 *
 * @see open
 * @see mkostemps
 * @see mkstemp
 * @see mkstemps
 *
 * @returns a `FileDescriptor` and the actual path.
 */
function mkostemp(string $template, int $flags): (FileDescriptor, string) {
  return mkostemps($template, /* suffix_len = */ 0, $flags);
}

/** Create a temporary file using a template filename.
 *
 * The template must end with exactly 6 `X` characters; the template may be
 * either a relative or absolute path, however the parent directory must already
 * exist.
 *
 * The temporary file:
 * - will be a new file (i.e. `O_CREAT | O_EXCL`)
 * - be owned by the current user
 * - be created with mode 0600
 *
 * @see mkostemp
 * @see mkostemps
 * @see mkstemps
 *
 * @returns a `FileDescriptor` and the actual path.
 */
function mkstemp(string $template): (FileDescriptor, string) {
  return mkostemps($template, /* suffix_len = */ 0, /* flags = */ 0);
}
