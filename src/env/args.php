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
 * Returns the arguments which this program was started with (normally passed via the command line).
 */
function args()[globals]: vec<string> {
    if (\HH\global_key_exists('argv')) {
        /* HH_IGNORE_ERROR[4110] */
        return vec(\HH\global_get('argv'));
    }

    return vec[];
}
