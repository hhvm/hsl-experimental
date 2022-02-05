<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */
use type Facebook\HackTest\HackTest;
use function HH\Lib\Experimental\OS\posix_spawnp;
use type HH\Lib\Experimental\OS\posix_spawn_file_actions_adddup2;
use namespace HH\Lib\IO;
use function Facebook\FBExpect\expect;
use namespace \HH\Lib\_Private\_OS;

final class SpawnTest extends HackTest {
  public async function testBasicUsage(): Awaitable<void> {
    list($read_pipe, $write_pipe) = IO\pipe();

    using $read_pipe->closeWhenDisposed();
    try {
      $pid = posix_spawnp(
        "ls",
        vec[
          new posix_spawn_file_actions_adddup2(
            $write_pipe->getFileDescriptor(),
            _OS\STDOUT_FILENO,
          ),
        ],
        shape(),
        vec["ls", "src/os"],
        vec[],
      );
    } finally {
      $write_pipe->close();
    }
    try {
      $output = await $read_pipe->readAllAsync();
    } finally {
      $status = null;
      \pcntl_waitpid($pid, inout $status);
    }
    expect(\pcntl_wexitstatus($status))->toEqual(0);
    expect($output)->toContainSubstring("spawn.php");
  }

  public async function testPipeUsage(): Awaitable<void> {
    list($final_read_pipe, $ls_write_pipe) = IO\pipe();
    list($ls_read_pipe, $find_write_pipe) = IO\pipe();

    try {
      $find_process = posix_spawnp(
        "find",
        vec[
          new posix_spawn_file_actions_adddup2(
            $find_write_pipe->getFileDescriptor(),
            _OS\STDOUT_FILENO,
          ),
        ],
        shape(),
        vec["find", "src/os", "-type", "f", "-print0"],
        vec[],
      );
    } finally {
      $find_write_pipe->close();
    }
    try {
      try {
        $cat_process = posix_spawnp(
          "xargs",
          vec[
            new posix_spawn_file_actions_adddup2(
              $ls_write_pipe->getFileDescriptor(),
              _OS\STDOUT_FILENO,
            ),
            new posix_spawn_file_actions_adddup2(
              $ls_read_pipe->getFileDescriptor(),
              _OS\STDIN_FILENO,
            ),
          ],
          shape(),
          vec["xargs", "-0", "cat"],
          vec[],
        );
      } finally {
        $ls_read_pipe->close();
        $ls_write_pipe->close();
      }
      try {
        $output = await $final_read_pipe->readAllAsync();
      } finally {
        $cat_status = null;
        \pcntl_waitpid($cat_process, inout $cat_status);
      }
    } finally {
      $find_status = null;
      \pcntl_waitpid($find_process, inout $find_status);
    }
    expect(\pcntl_wexitstatus($find_status))->toEqual(0);
    expect(\pcntl_wexitstatus($cat_status))->toEqual(0);
    expect($output)->toContainSubstring("function posix_spawnp");
  }
}
