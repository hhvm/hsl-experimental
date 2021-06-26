<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Env;

/**
 * Returns the value of the "TMPDIR" environment variable if it is set, otherwise it returns /tmp.
 *
 * @note On windows, we can't count on the environment variables "TEMP" or "TMP",
 *      and so must make the Win32 API call to get the default directory for temporary files.
 *
 * @note The return value of this function can be overridden using the sys_temp_dir ini directive.
 *
 * @see https://www.php.net/manual/en/function.sys-get-temp-dir.php
 */
function temp_dir(): string {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \sys_get_temp_dir();
}
