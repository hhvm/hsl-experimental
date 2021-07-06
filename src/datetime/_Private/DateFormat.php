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

interface DateFormat {
  public function format_a(): string;
  public function format_b(): string;
  public function format_c(): string;
  public function format_d(): string;
  public function format_e(): string;
  public function format_g(): string;
  public function format_h(): string;
  public function format_j(): string;
  public function format_k(): string;
  public function format_l(): string;
  public function format_m(): string;
  public function format_n(): string;
  public function format_p(): string;
  public function format_r(): string;
  public function format_t(): string;
  public function format_u(): string;
  public function format_w(): string;
  public function format_x(): string;
  public function format_y(): string;
  public function format_upcase_a(): string;
  public function format_upcase_b(): string;
  public function format_upcase_c(): string;
  public function format_upcase_d(): string;
  public function format_upcase_f(): string;
  public function format_upcase_g(): string;
  public function format_upcase_h(): string;
  public function format_upcase_i(): string;
  public function format_upcase_m(): string;
  public function format_upcase_p(): string;
  public function format_upcase_r(): string;
  public function format_upcase_s(): string;
  public function format_upcase_t(): string;
  public function format_upcase_u(): string;
  public function format_upcase_v(): string;
  public function format_upcase_w(): string;
  public function format_upcase_x(): string;
  public function format_upcase_y(): string;
  public function format_0x25(): string;
}

interface ZonedDateFormat extends DateFormat {
  public function format_s(): string;
  public function format_z(): string;
  public function format_upcase_z(): string;
}
