#!/usr/bin/env python3
"""Local cron commands. Extends EappointmentCli and repeats zmsbackend cron jobs."""

import os
import subprocess
import time
from datetime import datetime, timedelta

import click

from cli_base import EappointmentCli, print_info


def seconds_until_next_mark(interval_minutes: int) -> float:
  """Seconds until the next clock minute that is a multiple of the interval."""
  now = datetime.now()
  slot = (now.minute // interval_minutes + 1) * interval_minutes
  if slot >= 60:
    target = now.replace(minute=0, second=0, microsecond=0) + timedelta(hours=1)
  else:
    target = now.replace(minute=slot, second=0, microsecond=0)
  return max(0.0, (target - now).total_seconds())


class CronCli(EappointmentCli):
  """Repeat the local zmsbackend minutely cron on the deployed 5-minute marks."""

  MINUTELY_INTERVAL_MINUTES = 5

  def run_minutely(self, once: bool) -> None:
    """Run zmsbackend/cron/cronjob.minutly now, then on every 5th minute."""
    script = os.path.join(self.repo_dir, "zmsbackend", "cron", "cronjob.minutly")
    if not os.path.isfile(script):
      raise click.ClickException(f"Missing {script}")
    env = os.environ.copy()
    env.setdefault("ZMS_CRONROOT", "1")
    env.setdefault("ZMS_ENV", "dev")
    if once:
      print_info("Running zmsbackend/cron/cronjob.minutly once.")
    else:
      print_info("Running zmsbackend/cron/cronjob.minutly now, then every 5 minutes. Stop with Ctrl-C.")
    try:
      while True:
        started_at = time.monotonic()
        started = datetime.now().strftime("%H:%M:%S")
        print_info(f"cronjob.minutly starting at {started}")
        completed = subprocess.run(["bash", script], cwd=self.repo_dir, env=env, check=False)
        elapsed = time.monotonic() - started_at
        finished = datetime.now().strftime("%H:%M:%S")
        print_info(
          f"cronjob.minutly finished at {finished} in {elapsed:.0f}s (exit {completed.returncode})"
        )
        if once:
          return
        delay = seconds_until_next_mark(self.MINUTELY_INTERVAL_MINUTES)
        next_at = datetime.now() + timedelta(seconds=delay)
        print_info(f"Next run at {next_at.strftime('%H:%M:%S')}")
        time.sleep(delay)
    except KeyboardInterrupt:
      print_info("Stopped.")

  def register_cron(self, cli: click.Group) -> None:
    """Attach the ``cron`` command group."""
    app = self

    @cli.group("cron")
    def cli_cron():
      """Run local zmsbackend cron jobs."""
      pass

    @cli_cron.command("minutly")
    @click.option("--once", is_flag=True, default=False, help="Run one pass and exit.")
    def cli_cron_minutly(once: bool):
      """Run zmsbackend/cron/cronjob.minutly now, then every 5 minutes."""
      app.run_minutely(once)
