<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\File;

use namespace HH\Lib\{Str, Vec};

// <<__Const>>
final class Path {
  public function __construct(private string $path) {}

  public function toString(): string {
    return $this->path;
  }

  public function isAbsolute(): bool {
    return Str\starts_with($this->path, \DIRECTORY_SEPARATOR);
  }

  public function isRelative(): bool {
    return !$this->isAbsolute();
  }

  public function isDirectory(): bool {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    return \is_dir($this->path);
  }

  public function isFile(): bool {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    return \is_file($this->path);
  }

  public function isSymlink(): bool {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    return \is_link($this->path);
  }

  public function exists(): bool {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    return \file_exists($this->path);
  }

  public function getParent(): Path {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    return new Path(\dirname($this->path));
  }

  public function getBaseName(): string {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    return \basename($this->path);
  }

  public function getExtension(): ?string {
    /* HH_FIXME[2049] calling stdlib directly */
    /* HH_FIXME[4107] calling stdlib directly */
    $extension = \pathinfo($this->path, \PATHINFO_EXTENSION);
    return $extension === '' ? null : $extension;
  }

  public function getParts(): vec<string> {
    return Str\split($this->path, \DIRECTORY_SEPARATOR)
      |> Vec\filter($$, $part ==> !Str\is_empty($part));
  }

  public function withExtension(string $extension): Path {
    $extension = Str\strip_prefix($extension, '.');
    $new_path = $this->path;
    $current_extension = $this->getExtension();
    if ($current_extension !== null) {
      $new_path = Str\strip_suffix($new_path, '.'.$current_extension);
    }
    return new Path($new_path.'.'.$extension);
  }
}
