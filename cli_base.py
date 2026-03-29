#!/usr/bin/env python3
"""Core CLI: repo helpers, database / Flyway test data, modules, clean."""

import json
import os
import shlex
import socket
import subprocess
import sys

import click
from colorama import Fore, Back, Style


def print_info(string):
  print(Back.RED + "zmslocaldev" + Style.RESET_ALL + " " + Fore.YELLOW + string + Style.RESET_ALL)


class EappointmentCli:
  """Base CLI: paths, DB/Flyway test data import, module and clean commands."""

  MODULES = [
    "mellon",
    "zmsadmin",
    "zmsapi",
    "zmscalldisplay",
    "zmscitizenapi",
    "zmsclient",
    "zmsdb",
    "zmsdldb",
    "zmsentities",
    "zmsmessaging",
    "zmsslim",
    "zmsstatistic",
    "zmsticketprinter",
  ]

  def __init__(self, repo_dir: str):
    self.repo_dir = repo_dir

  @staticmethod
  def parse_mysql_port(value: str) -> str:
    if not value:
      return "3306"
    if value.startswith("tcp://"):
      return value.rsplit(":", 1)[-1]
    return value

  @staticmethod
  def _default_mysql_host_when_unset() -> str:
    """If ``MYSQL_HOST`` is unset: use Docker service ``db`` when that name resolves; else localhost (Mac/host)."""
    try:
      socket.gethostbyname("db")
    except OSError:
      return "127.0.0.1"
    return "db"

  def db_env(
    self,
    mysql_host=None,
    mysql_port=None,
    mysql_database=None,
    mysql_user=None,
    mysql_password=None,
  ):
    """Resolve DB connection. When MYSQL_HOST is unset, prefers ``db`` if resolvable (Docker), else 127.0.0.1."""
    if mysql_host is None:
      env_host = os.getenv("MYSQL_HOST")
      if not env_host:
        host = self._default_mysql_host_when_unset()
        if host == "127.0.0.1":
          print_info("MYSQL_HOST unset; hostname 'db' not resolvable — using 127.0.0.1 for MySQL.")
      else:
        host = env_host
    else:
      host = mysql_host
    port = self.parse_mysql_port(os.getenv("MYSQL_PORT", "3306") if mysql_port is None else mysql_port)
    name = os.getenv("MYSQL_DATABASE", "db") if mysql_database is None else mysql_database
    user = os.getenv("MYSQL_USER", "db") if mysql_user is None else mysql_user
    password = os.getenv("MYSQL_PASSWORD", "db") if mysql_password is None else mysql_password
    return host, port, name, user, password

  def run_cmd(self, args, cwd=None, env=None):
    print_info("Running: " + " ".join(args))
    subprocess.run(args, cwd=cwd, env=env, check=True)

  def apply_appointment_email_link_config(self, appointment_links_host: str, **db_kw):
    """Set config keys used in appointment confirmation/change emails (host:port, no scheme)."""
    db_host, db_port, db_name, db_user, db_password = self.db_env(**db_kw)
    esc = appointment_links_host.replace("\\", "\\\\").replace("'", "''")
    sql = f"""UPDATE `config`
SET `value` = '{esc}',
    `changeTimestamp` = NOW()
WHERE `name` = 'appointments__urlAppointments';

UPDATE `config`
SET `value` = '{esc}',
    `changeTimestamp` = NOW()
WHERE `name` = 'appointments__urlChange';
"""
    print_info(
      "Updating config for email confirmation links "
      f"(appointments__urlAppointments / appointments__urlChange → {appointment_links_host!r})..."
    )
    subprocess.run(
      [
        "mysql",
        "--ssl=0",
        "-h", db_host,
        "-P", db_port,
        "-u", db_user,
        f"--password={db_password}",
        db_name,
      ],
      input=sql.encode("utf-8"),
      check=True,
    )

  def run_flyway_test_data_migrations(
    self,
    citizenview_base_url=None,
    appointment_links_host="localhost:8082/",
    mysql_host=None,
    mysql_port=None,
    mysql_database=None,
    mysql_user=None,
    mysql_password=None,
  ):
    db_kw = dict(
      mysql_host=mysql_host,
      mysql_port=mysql_port,
      mysql_database=mysql_database,
      mysql_user=mysql_user,
      mysql_password=mysql_password,
    )
    host, port, name, user, password = self.db_env(**db_kw)
    flyway_dir = os.path.join(self.repo_dir, "zmsautomation", "src", "main", "resources", "db", "migration")
    zmsautomation_dir = os.path.join(self.repo_dir, "zmsautomation")
    citizenview = citizenview_base_url or os.getenv("CITIZENVIEW_BASE_URI", "http://citizenview:8082/")

    self.run_cmd([
      "mvn", "-B", "-q",
      f"-Dflyway.url=jdbc:mysql://{host}:{port}/{name}",
      f"-Dflyway.user={user}",
      f"-Dflyway.password={password}",
      f"-Dflyway.locations=filesystem:{flyway_dir}",
      f"-Dflyway.placeholders.citizenviewBaseUrl={citizenview}",
      "-Dflyway.baselineOnMigrate=true",
      "flyway:migrate",
    ], cwd=zmsautomation_dir)

    self.apply_appointment_email_link_config(appointment_links_host, **db_kw)

  def clear_local_cache_folder(self):
    cache_dir = os.path.join(self.repo_dir, "cache", "@")
    if not os.path.isdir(cache_dir):
      print_info(f"Cache folder does not exist, skipping: {cache_dir}")
      return

    removed_entries = 0
    for entry in os.listdir(cache_dir):
      entry_path = os.path.join(cache_dir, entry)
      if os.path.isdir(entry_path):
        subprocess.run(["rm", "-rf", entry_path], check=True)
      else:
        os.remove(entry_path)
      removed_entries += 1

    print_info(f"Cleared {removed_entries} cache entries from {cache_dir}")

  def clear_all_database_tables(self, **db_kw):
    host, port, name, user, password = self.db_env(**db_kw)
    drop_sql = """SET FOREIGN_KEY_CHECKS = 0;
SET SESSION group_concat_max_len = 1000000;
SET @tables = (SELECT GROUP_CONCAT(CONCAT('`', table_name, '`')) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE');
SET @sql = IF(@tables IS NULL OR @tables = '', 'DO 0', CONCAT('DROP TABLE IF EXISTS ', @tables));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET FOREIGN_KEY_CHECKS = 1;"""
    print_info("Dropping all existing database tables...")
    subprocess.run(
      ["mysql", "--ssl=0", "-h", host, "-P", port, "-u", user, f"--password={password}", name],
      input=drop_sql.encode("utf-8"),
      check=True,
    )
    print_info("Database tables dropped.")

  def register(self, cli: click.Group) -> None:
    """Attach modules, db, clean, and modules loop commands to the root group."""
    app = self

    @cli.group("modules")
    def cli_modules():
      pass

    @cli_modules.command("clear-local-cache")
    def cli_modules_clear_local_cache():
      """Clear local cache/@ directory."""
      app.clear_local_cache_folder()

    @cli.group("db")
    def cli_db():
      """Database setup helpers for local development and tests."""
      pass

    @cli_db.command("migrate-test-data")
    @click.option("--citizenview-base-url", default=None, help="Flyway placeholder citizenviewBaseUrl (default: from env or http://citizenview:8082/).")
    @click.option(
      "--appointment-links-host",
      default="localhost:8082/",
      show_default=True,
      help="Host:port written to appointments__urlAppointments and appointments__urlChange (email confirmation links).",
    )
    @click.option(
      "--mysql-host",
      default=None,
      help="Override MYSQL_HOST (default: env MYSQL_HOST or 'db' in Docker). On the Mac host use 127.0.0.1 if MySQL is published on localhost.",
    )
    @click.option("--mysql-port", default=None, help="Override MYSQL_PORT (default: env or 3306).")
    @click.option("--mysql-database", default=None, help="Override MYSQL_DATABASE.")
    @click.option("--mysql-user", default=None, help="Override MYSQL_USER.")
    @click.option("--mysql-password", default=None, help="Override MYSQL_PASSWORD.")
    def cli_db_migrate_test_data(
      citizenview_base_url,
      appointment_links_host,
      mysql_host,
      mysql_port,
      mysql_database,
      mysql_user,
      mysql_password,
    ):
      """Run Flyway migrations used for test data setup."""
      app.run_flyway_test_data_migrations(
        citizenview_base_url,
        appointment_links_host=appointment_links_host,
        mysql_host=mysql_host,
        mysql_port=mysql_port,
        mysql_database=mysql_database,
        mysql_user=mysql_user,
        mysql_password=mysql_password,
      )

    @cli_db.command("drop-all-tables")
    @click.option("--mysql-host", default=None, help="Override MYSQL_HOST (use 127.0.0.1 on the host).")
    @click.option("--mysql-port", default=None, help="Override MYSQL_PORT.")
    @click.option("--mysql-database", default=None, help="Override MYSQL_DATABASE.")
    @click.option("--mysql-user", default=None, help="Override MYSQL_USER.")
    @click.option("--mysql-password", default=None, help="Override MYSQL_PASSWORD.")
    def cli_db_clear_all_tables(mysql_host, mysql_port, mysql_database, mysql_user, mysql_password):
      """Drop all tables in configured database."""
      app.clear_all_database_tables(
        mysql_host=mysql_host,
        mysql_port=mysql_port,
        mysql_database=mysql_database,
        mysql_user=mysql_user,
        mysql_password=mysql_password,
      )

    @cli_db.command("full-setup")
    @click.option("--city", default="munich", show_default=True)
    @click.option("--skip-import", is_flag=True, default=False)
    @click.option("--skip-flyway", is_flag=True, default=False)
    @click.option("--skip-php-migrate", is_flag=True, default=False)
    @click.option("--skip-hourly", is_flag=True, default=False)
    @click.option("--skip-minutly", is_flag=True, default=False)
    @click.option("--skip-clear-cache", is_flag=True, default=False)
    @click.option("--citizenview-base-url", default=None, help="Flyway placeholder citizenviewBaseUrl (default: from env or http://citizenview:8082/).")
    @click.option(
      "--appointment-links-host",
      default="localhost:8082/",
      show_default=True,
      help="Host:port for appointments__urlAppointments / appointments__urlChange (after Flyway test migrations).",
    )
    @click.option("--mysql-host", default=None, help="Override MYSQL_HOST (use 127.0.0.1 on the host).")
    @click.option("--mysql-port", default=None, help="Override MYSQL_PORT.")
    @click.option("--mysql-database", default=None, help="Override MYSQL_DATABASE.")
    @click.option("--mysql-user", default=None, help="Override MYSQL_USER.")
    @click.option("--mysql-password", default=None, help="Override MYSQL_PASSWORD.")
    def cli_db_full_setup(
      city,
      skip_import,
      skip_flyway,
      skip_php_migrate,
      skip_hourly,
      skip_minutly,
      skip_clear_cache,
      citizenview_base_url,
      appointment_links_host,
      mysql_host,
      mysql_port,
      mysql_database,
      mysql_user,
      mysql_password,
    ):
      """Import base DB and run full local setup sequence."""
      db_kw = dict(
        mysql_host=mysql_host,
        mysql_port=mysql_port,
        mysql_database=mysql_database,
        mysql_user=mysql_user,
        mysql_password=mysql_password,
      )
      host, port, name, user, password = app.db_env(**db_kw)
      base_sql = os.path.join(app.repo_dir, ".resources", "zms.sql")
      zmsapi_dir = os.path.join(app.repo_dir, "zmsapi")
      run_env = os.environ.copy()
      run_env.setdefault("ZMS_CRONROOT", "1")
      run_env.setdefault("ZMS_ENV", "dev")

      if not skip_import:
        if not os.path.isfile(base_sql):
          raise FileNotFoundError(f"Base SQL file not found: {base_sql}")
        app.clear_all_database_tables(**db_kw)
        print_info(f"Importing base database from {base_sql}")
        with open(base_sql, "rb") as f:
          subprocess.run(
            [
              "mysql",
              "--ssl=0",
              "--init-command=SET SESSION FOREIGN_KEY_CHECKS=0;",
              "-h", host,
              "-P", port,
              "-u", user,
              f"--password={password}",
              name,
            ],
            stdin=f,
            check=True,
          )

      if not skip_flyway:
        app.run_flyway_test_data_migrations(
          citizenview_base_url,
          appointment_links_host=appointment_links_host,
          **db_kw,
        )

      if not skip_php_migrate:
        app.run_cmd(["vendor/bin/migrate", "--update"], cwd=zmsapi_dir, env=run_env)

      if not skip_hourly:
        app.run_cmd(["./cron/cronjob.hourly", f"--city={city}"], cwd=zmsapi_dir, env=run_env)

      if not skip_minutly:
        app.run_cmd(["./cron/cronjob.minutly"], cwd=zmsapi_dir, env=run_env)

      if not skip_clear_cache:
        app.clear_local_cache_folder()

      print_info("Database full setup completed.")

    @cli_modules.command("reference-libraries")
    @click.option("--no-symlink", is_flag=True, show_default=True, default=False)
    def cli_modules_reference_libraries(no_symlink: bool):
      module_dependencies = {}
      for module in app.MODULES:
        composer_file_path = os.path.join(app.repo_dir, module, "composer.json")
        with open(composer_file_path, "r") as f:
          composer_content = json.load(f)

        composer_content["repositories"] = list([i for i in composer_content.get("repositories", []) if i.get("type") != "path" and i.get("url", None) != "../*"])
        composer_content["repositories"].append({
          "type": "path",
          "url": "../*",
          "options": {
            "symlink": not no_symlink
          }
        })

        require = composer_content.get("require", [])
        for dependency_key in require:
          if dependency_key.startswith("eappointment/"):
            if module not in module_dependencies:
              module_dependencies[module] = []
            module_dependencies[module].append(dependency_key)
            require[dependency_key] = "@dev"
        composer_content["require"] = require

        with open(composer_file_path, "w") as f:
          json.dump(composer_content, f, indent=4)
          f.write("\n")

      for module in module_dependencies:
        module_dir = os.path.join(app.repo_dir, module)
        os.system(f"cd {module_dir} && composer update {' '.join(module_dependencies[module])} --no-scripts --no-plugins 1>/dev/null")

    @cli_modules.command("check-upgrade")
    @click.argument("php_version")
    def cli_modules_check_upgrade(php_version):
      """Check dependencies for upgrade to specified PHP version without actually upgrading."""
      for module in app.MODULES:
        print_info(f"Checking module: {module}")
        composer_file_path = os.path.join(app.repo_dir, module, "composer.json")

        with open(composer_file_path, "r") as f:
          composer_content = json.load(f)

        current_php_version = composer_content.get("config", {}).get("platform", {}).get("php")

        if current_php_version is None:
          print_info(f"{module}: current PHP version is not set, skipping...")
          continue

        print_info(f"{module}: {current_php_version} -> {php_version}")

        if "config" not in composer_content:
          composer_content["config"] = {}
        if "platform" not in composer_content["config"]:
          composer_content["config"]["platform"] = {}
        composer_content["config"]["platform"]["php"] = php_version

        with open(composer_file_path, "w") as f:
          json.dump(composer_content, f, indent=4)

        os.system(f"cd {os.path.join(app.repo_dir, module)} && composer outdated")

        composer_content["config"]["platform"]["php"] = current_php_version
        with open(composer_file_path, "w") as f:
          json.dump(composer_content, f, indent=4)

    @cli.group("clean")
    def cli_clean():
      pass

    @cli_clean.command("cache")
    def cli_clean_cache():
      for module in app.MODULES:
        os.system(f"rm -rf {module}/cache/*")
      print_info("Cache cleaned")

    @cli_modules.command("loop")
    @click.argument("commands", required=True, nargs=-1)
    def cli_modules_loop(commands):
      """Loops through repositories and executes a given command. Adds --legacy-peer-deps for npm install commands."""
      specific_modules = ["zmsadmin", "zmscalldisplay", "zmsstatistic", "zmsticketprinter"]

      is_npm_command = commands[0] == "npm"
      is_composer_install = len(commands) >= 2 and commands[0] == "composer" and commands[1] == "install"

      build_commands = {
        "zmsadmin": ["npm run build"],
        "zmscalldisplay": ["npm run build"],
        "zmsstatistic": ["npm run build"],
        "zmsticketprinter": ["npm run build"]
      }

      target_modules = specific_modules if is_npm_command else app.MODULES

      for module in target_modules:
        print_info(f"Repository: {module}")
        module_dir = os.path.join(app.repo_dir, module)
        os.chdir(module_dir)

        if is_npm_command and commands[1] == "install":
          modified_commands = list(commands) + ["--legacy-peer-deps"]
          os.system(" ".join(modified_commands))
        elif is_npm_command and len(commands) > 1 and commands[1] == "build":
          print_info(f"Running custom npm build commands for {module}")
          for build_command in build_commands.get(module, []):
            os.system(build_command)
        else:
          os.system(" ".join(commands))

        if is_composer_install:
          vendor_bin = os.path.join(module_dir, "vendor", "bin")
          if os.path.isdir(vendor_bin):
            for script in os.listdir(vendor_bin):
              script_path = os.path.join(vendor_bin, script)
              if os.path.isfile(script_path):
                os.chmod(script_path, 0o755)

    @cli.group("dev")
    def cli_dev():
      """Chained local dev tasks (same as running ./cli elsewhere in this repo)."""
      pass

    @cli_dev.command("setup-local")
    @click.option("--skip-composer", is_flag=True, default=False, help="Skip ./cli modules loop composer install.")
    @click.option("--skip-npm-install", is_flag=True, default=False, help="Skip ./cli modules loop npm install.")
    @click.option("--skip-npm-build", is_flag=True, default=False, help="Skip ./cli modules loop npm build.")
    @click.option("--skip-db", is_flag=True, default=False, help="Skip ./cli db full-setup.")
    @click.option(
      "--db-args",
      default="",
      help="Extra arguments for ./cli db full-setup (shell-quoted), e.g. --citizenview-base-url http://citizenview:8082/",
    )
    def cli_dev_setup_local(
      skip_composer,
      skip_npm_install,
      skip_npm_build,
      skip_db,
      db_args,
    ):
      """db full-setup first (import + migrations), then composer install, npm install, npm build."""
      cli_exe = os.path.join(app.repo_dir, "cli")
      if not skip_db:
        full_setup = [cli_exe, "db", "full-setup"]
        extra = (db_args or "").strip()
        if extra:
          full_setup.extend(shlex.split(extra))
        app.run_cmd(full_setup, cwd=app.repo_dir)
      if not skip_composer:
        app.run_cmd([cli_exe, "modules", "loop", "composer", "install"], cwd=app.repo_dir)
      if not skip_npm_install:
        app.run_cmd([cli_exe, "modules", "loop", "npm", "install"], cwd=app.repo_dir)
      if not skip_npm_build:
        app.run_cmd([cli_exe, "modules", "loop", "npm", "build"], cwd=app.repo_dir)
      if skip_composer and skip_npm_install and skip_npm_build and skip_db:
        raise click.ClickException("Nothing to run; remove some --skip-* flags.")
      print_info("Local dev setup finished.")
