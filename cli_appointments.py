#!/usr/bin/env python3
"""Local appointment commands. Extends EappointmentCli and runs the tools/ fillers."""

import os
import shlex
import subprocess
import sys

import click

from cli_base import EappointmentCli, print_info


class AppointmentsCli(EappointmentCli):
  """Create local appointments through zmsapi."""

  # Inside zms-web Apache is port 80; compose publishes it as 8090 on the host.
  LOCAL_API_IN_CONTAINER = "http://127.0.0.1/terminvereinbarung/api/2"
  LOCAL_API_ON_HOST = "http://127.0.0.1:8090/terminvereinbarung/api/2"
  LOCAL_FILL_USERNAME = "_system_queuefill"
  LOCAL_FILL_PASSWORD = "vorschau"

  def local_zmsapi_base_url(self) -> str:
    """API base for appointment fillers. ZMS_API_URL wins; otherwise container port 80 or host port 8090."""
    configured = os.getenv("ZMS_API_URL", "").strip()
    if configured:
      return configured.rstrip("/")
    if os.path.exists("/.dockerenv"):
      return self.LOCAL_API_IN_CONTAINER
    return self.LOCAL_API_ON_HOST

  def run_appointment_tool(self, script_name: str, arguments: list[str], password: str) -> None:
    """Run a tools/ filler. The password stays in the environment, not in the printed command."""
    script = os.path.join(self.repo_dir, "tools", script_name)
    if not os.path.isfile(script):
      raise click.ClickException(f"Missing {script}")
    os.makedirs(os.path.join(self.repo_dir, "cache"), exist_ok=True)
    env = os.environ.copy()
    env["FILL_QUEUE_PASSWORD"] = password
    env["BOOK_PASSWORD"] = password
    env.setdefault("ENVIRONMENT", "local")
    printable = " ".join(shlex.quote(part) for part in [sys.executable, script, *arguments])
    print_info("Running: " + printable)
    subprocess.run([sys.executable, script, *arguments], cwd=self.repo_dir, env=env, check=True)

  def register_appointments(self, cli: click.Group) -> None:
    """Attach the ``appointments`` command group."""
    app = self

    @cli.group("appointments")
    def cli_appointments():
      """Create local appointments through zmsapi."""
      pass

    @cli_appointments.command("create")
    @click.option("--target", type=click.Choice(["scope", "cluster"]), default="scope", show_default=True)
    @click.option("--target-id", type=int, required=True, help="Scope id or cluster id.")
    @click.option(
      "--mode",
      type=click.Choice(["spontaneous", "appointments", "mixed", "fill_capacity"]),
      default="spontaneous",
      show_default=True,
    )
    @click.option("--count", type=int, default=10, show_default=True, help="Total to create. Ignored for fill_capacity.")
    @click.option("--day", default="today", show_default=True, help="today or YYYY-MM-DD. No past days.")
    @click.option("--service-name", default=None, help="Pin every process to this service.")
    @click.option("--name-prefix", default="LOADTEST-local", show_default=True)
    @click.option("--dry-run", is_flag=True, default=False, help="Plan only, do not book.")
    @click.option("--wipe-queue", type=click.Choice(["none", "before", "only"]), default="none", show_default=True)
    @click.option("--wipe-all", is_flag=True, default=False, help="With --wipe-queue, delete every deletable process that day.")
    @click.option("--base-url", default=None, help="zmsapi base. Default: local container or http://127.0.0.1:8090.")
    @click.option("--username", default=app.LOCAL_FILL_USERNAME, show_default=True)
    @click.option("--password", default=app.LOCAL_FILL_PASSWORD, show_default=True, help="Local password login. Not printed.")
    def cli_appointments_create(
      target,
      target_id,
      mode,
      count,
      day,
      service_name,
      name_prefix,
      dry_run,
      wipe_queue,
      wipe_all,
      base_url,
      username,
      password,
    ):
      """Create walk-ins or timed appointments for one scope or one cluster."""
      arguments = [
        "--base-url", base_url or app.local_zmsapi_base_url(),
        "--username", username,
        "--target", target,
        "--target-id", str(target_id),
        "--mode", mode,
        "--count", str(count),
        "--day", day,
        "--name-prefix", name_prefix,
        "--wipe-queue", wipe_queue,
        "--report", os.path.join(app.repo_dir, "cache", "fill-queue-report.json"),
      ]
      if service_name:
        arguments.extend(["--service-name", service_name])
      if dry_run:
        arguments.append("--dry-run")
      if wipe_all:
        arguments.append("--wipe-all")
      app.run_appointment_tool("fill-queue-appointments", arguments, password)

    @cli_appointments.command("fill-opening-hours")
    @click.option("--from-day", default="today", show_default=True, help="First day (today or YYYY-MM-DD). No past days.")
    @click.option("--days", type=int, default=None, help="Last day as an offset from today. 0 = today only. Default: each scope's bookable end.")
    @click.option("--scope-ids", default="", help="Comma-separated scope ids. Empty fills every scope.")
    @click.option("--service-name", default=None, help="Only this service (exact name, else substring).")
    @click.option("--max-bookings", type=int, default=None, help="Stop after this many confirmed appointments.")
    @click.option("--workers", type=int, default=None, help="Scope-days in flight. Default 16, max 32. 8 is usually faster on the local database.")
    @click.option("--pause", type=float, default=0, show_default=True, help="Seconds between reserve and confirm. 0 is fine locally.")
    @click.option("--name-prefix", default="LOADTEST-local", show_default=True)
    @click.option("--dry-run", is_flag=True, default=False, help="Count free seats and do not reserve.")
    @click.option("--base-url", default=None, help="zmsapi base. Default: local container or http://127.0.0.1:8090.")
    @click.option("--username", default=app.LOCAL_FILL_USERNAME, show_default=True)
    @click.option("--password", default=app.LOCAL_FILL_PASSWORD, show_default=True, help="Local password login. Not printed.")
    def cli_appointments_fill_opening_hours(
      from_day,
      days,
      scope_ids,
      service_name,
      max_bookings,
      workers,
      name_prefix,
      dry_run,
      pause,
      base_url,
      username,
      password,
    ):
      """Book every remaining opening-hour seat. Narrow with --scope-ids and --days; an empty scope list fills every scope."""
      arguments = [
        "--base-url", base_url or app.local_zmsapi_base_url(),
        "--username", username,
        "--from-day", from_day,
        "--name-prefix", name_prefix,
        "--pause", str(pause),
        "--report", os.path.join(app.repo_dir, "cache", "fill-opening-hours-report.json"),
      ]
      if days is not None:
        arguments.extend(["--days", str(days)])
      if scope_ids:
        arguments.extend(["--scope-ids", scope_ids])
      if service_name:
        arguments.extend(["--service-name", service_name])
      if max_bookings is not None:
        arguments.extend(["--max-bookings", str(max_bookings)])
      if workers is not None:
        arguments.extend(["--workers", str(workers)])
      if dry_run:
        arguments.append("--dry-run")
      app.run_appointment_tool("fill-opening-hours", arguments, password)
