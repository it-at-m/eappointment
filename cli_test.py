#!/usr/bin/env python3
"""ATAF / zmsautomation test commands (macOS host). Extends EappointmentCli."""

import os
import platform
import re
import shutil
import subprocess
import sys
import tempfile
import urllib.error
import urllib.request
import zipfile
from pathlib import Path

import click

from cli_base import EappointmentCli, print_info


class TestCli(EappointmentCli):
  """CLI extension: local ATAF runs, WebDrivers, gateway TLS truststore."""

  RUN_MAC_LOCAL_BASE_URI = "https://localhost:8091/terminvereinbarung/api/2"
  RUN_MAC_LOCAL_CITIZEN_API_URI = "https://localhost:8091/terminvereinbarung/api/citizen"
  RUN_MAC_LOCAL_ADMIN_URI = "https://localhost:8091/terminvereinbarung/admin/"
  RUN_MAC_LOCAL_STATISTIC_URI = "https://localhost:8091/terminvereinbarung/statistic/"
  RUN_MAC_LOCAL_ADMIN_URI_HTTP = "http://localhost:8080/terminvereinbarung/admin/"
  RUN_MAC_LOCAL_STATISTIC_URI_HTTP = "http://localhost:8080/terminvereinbarung/statistic/"
  RUN_MAC_LOCAL_CITIZEN_VIEW_URI = "http://localhost:8082/"

  LOCAL_GATEWAY_TRUSTSTORE_FILENAME = "cacerts-with-local-gateway-8091.jks"
  LOCAL_GATEWAY_CERT_ALIAS = "zms-localhost-8091-gateway"
  CACERTS_DEFAULT_PASSWORD = "changeit"

  DEFAULT_MSEDGE_DRIVER_VERSION = "146.0.3856.72"

  @staticmethod
  def needs_local_gateway_truststore(env, api_http):
    if api_http:
      return False
    for key in ("BASE_URI", "CITIZEN_API_BASE_URI"):
      v = env.get(key, "")
      if "https://localhost:8091" in v or "https://127.0.0.1:8091" in v:
        return True
    return False

  def prepare_local_gateway_truststore(self, java_home: str) -> str:
    openssl = shutil.which("openssl")
    keytool = os.path.join(java_home, "bin", "keytool")
    if not openssl:
      raise click.ClickException("openssl not found (needed to read the gateway cert). Install Xcode CLT or openssl.")
    if not os.path.isfile(keytool):
      raise click.ClickException(f"keytool not found: {keytool}")

    src_cacerts = Path(java_home) / "lib" / "security" / "cacerts"
    if not src_cacerts.is_file():
      raise click.ClickException(f"JDK cacerts not found: {src_cacerts}")

    cfg = Path.home() / ".config" / "eappointment"
    cfg.mkdir(parents=True, exist_ok=True)
    truststore = cfg / self.LOCAL_GATEWAY_TRUSTSTORE_FILENAME
    cert_pem = cfg / "localhost-8091-gateway.pem"

    pipe = (
      "echo | openssl s_client -connect localhost:8091 -servername localhost 2>/dev/null "
      "| openssl x509 -outform PEM"
    )
    r = subprocess.run(pipe, shell=True, capture_output=True, text=True, timeout=25)
    pem = (r.stdout or "").strip()
    if not pem or "BEGIN CERTIFICATE" not in pem:
      raise click.ClickException(
        "Could not read a TLS certificate from https://localhost:8091.\n"
        "Start your stack (refarch-gateway on 8091) and retry."
      )
    cert_pem.write_text(pem + "\n", encoding="utf-8")

    if not truststore.is_file():
      shutil.copy2(src_cacerts, truststore)

    subprocess.run(
      [
        keytool,
        "-delete",
        "-alias",
        self.LOCAL_GATEWAY_CERT_ALIAS,
        "-keystore",
        str(truststore),
        "-storepass",
        self.CACERTS_DEFAULT_PASSWORD,
      ],
      capture_output=True,
      check=False,
    )
    ir = subprocess.run(
      [
        keytool,
        "-importcert",
        "-noprompt",
        "-trustcacerts",
        "-alias",
        self.LOCAL_GATEWAY_CERT_ALIAS,
        "-file",
        str(cert_pem),
        "-keystore",
        str(truststore),
        "-storepass",
        self.CACERTS_DEFAULT_PASSWORD,
      ],
      capture_output=True,
      text=True,
      check=False,
    )
    if ir.returncode != 0:
      raise click.ClickException(
        "keytool -importcert failed:\n" + (ir.stderr or ir.stdout or "unknown error")
      )
    return str(truststore)

  @staticmethod
  def mac_require_darwin():
    if sys.platform != "darwin":
      raise click.ClickException("This command is only supported on macOS.")

  def resolve_mac_java_home(self):
    jh = (os.environ.get("JAVA_HOME") or "").strip()
    if jh and os.path.isfile(os.path.join(jh, "bin", "java")):
      return jh
    brew = shutil.which("brew")
    if brew:
      for formula in ("openjdk@21", "openjdk@25", "openjdk"):
        try:
          p = subprocess.run(
            [brew, "--prefix", formula],
            capture_output=True,
            text=True,
            check=True,
          ).stdout.strip()
          cand = os.path.join(p, "libexec", "openjdk.jdk", "Contents", "Home")
          if os.path.isfile(os.path.join(cand, "bin", "java")):
            return cand
        except (subprocess.CalledProcessError, OSError):
          continue
    mvn = shutil.which("mvn")
    if mvn:
      r = subprocess.run(["mvn", "-v"], capture_output=True, text=True)
      for pattern in (r"Java home:\s*(.+)", r"runtime:\s*(.+\.Home)"):
        m = re.search(pattern, r.stdout)
        if m:
          cand = m.group(1).strip()
          if os.path.isfile(os.path.join(cand, "bin", "java")):
            return cand
    raise click.ClickException(
      "Could not find a JDK (java). Install e.g.: brew install openjdk@21\n"
      "Then: export JAVA_HOME=\"$(brew --prefix openjdk@21)/libexec/openjdk.jdk/Contents/Home\""
    )

  @staticmethod
  def mac_default_msedgedriver_path():
    p = os.path.join(os.path.expanduser("~"), ".local", "bin", "msedgedriver")
    if os.path.isfile(p) and os.access(p, os.X_OK):
      return p
    return None

  def resolve_webdriver_paths(self):
    candidates = {
      "chrome": ("chromedriver", ["chromedriver"]),
      "firefox": ("geckodriver", ["geckodriver"]),
      "edge": ("msedgedriver", ["msedgedriver", "edgedriver"]),
    }
    out = {}
    for key, (label, names) in candidates.items():
      out[key] = None
      for name in names:
        p = shutil.which(name)
        if p:
          out[key] = p
          break
      if key == "edge" and not out[key] and sys.platform == "darwin":
        out[key] = self.mac_default_msedgedriver_path()
    return out

  @staticmethod
  def detect_microsoft_edge_version_mac():
    edge_bin = "/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge"
    if not os.path.isfile(edge_bin):
      return None
    try:
      r = subprocess.run(
        [edge_bin, "--version"],
        capture_output=True,
        text=True,
        timeout=15,
        check=False,
      )
      m = re.search(r"(\d+\.\d+\.\d+\.\d+)", r.stdout + r.stderr)
      if m:
        return m.group(1)
    except (OSError, subprocess.SubprocessError):
      pass
    return None

  @staticmethod
  def mac_msedgedriver_zip_name():
    if platform.machine().lower() == "arm64":
      return "edgedriver_mac64_m1.zip"
    return "edgedriver_mac64.zip"

  def install_msedgedriver_mac(self, version=None):
    self.mac_require_darwin()
    ver = version or self.detect_microsoft_edge_version_mac() or self.DEFAULT_MSEDGE_DRIVER_VERSION
    zip_name = self.mac_msedgedriver_zip_name()
    url = f"https://msedgedriver.microsoft.com/{ver}/{zip_name}"
    bindir = os.path.join(os.path.expanduser("~"), ".local", "bin")
    os.makedirs(bindir, exist_ok=True)
    dest = os.path.join(bindir, "msedgedriver")

    print_info(f"Downloading Microsoft Edge WebDriver {ver} ({zip_name}) …")
    try:
      with urllib.request.urlopen(url, timeout=120) as resp:
        data = resp.read()
    except urllib.error.HTTPError as e:
      raise click.ClickException(
        f"Could not download Edge WebDriver from:\n  {url}\n({e.code} {e.reason}).\n"
        "Install Microsoft Edge from https://www.microsoft.com/edge (so we can match versions), "
        "or pass --edge-driver-version with a build that exists on msedgedriver.microsoft.com."
      ) from e
    except urllib.error.URLError as e:
      raise click.ClickException(f"Download failed: {e}") from e

    with tempfile.TemporaryDirectory() as td:
      zpath = os.path.join(td, "edgedriver.zip")
      with open(zpath, "wb") as f:
        f.write(data)
      with zipfile.ZipFile(zpath, "r") as zf:
        zf.extractall(td)
      found = None
      for root, _, files in os.walk(td):
        if "msedgedriver" in files:
          found = os.path.join(root, "msedgedriver")
          break
      if not found:
        raise click.ClickException("Downloaded zip did not contain an msedgedriver binary.")
      shutil.copy2(found, dest)
      os.chmod(dest, 0o755)

    print_info(f"Installed: {dest}")
    print_info("Ensure ~/.local/bin is on PATH, e.g. add: export PATH=\"$HOME/.local/bin:$PATH\"")

  def enable_safari_webdriver_mac(self):
    """Run ``sudo safaridriver --enable`` so Selenium can drive Safari (ships with macOS)."""
    sd = shutil.which("safaridriver")
    if not sd:
      print_info(
        "safaridriver not on PATH (usually /usr/bin/safaridriver). "
        "Enable Safari → Settings → Advanced → Show Develop menu, then retry."
      )
      return
    sudo = shutil.which("sudo")
    if not sudo:
      print_info("sudo not found; run manually: sudo safaridriver --enable")
      return
    print_info("Enabling Safari WebDriver: sudo safaridriver --enable …")
    print_info("(Enter your macOS login password if prompted.)")
    # Do not capture stdout/stderr — sudo needs a TTY for the password prompt.
    r = subprocess.run([sudo, sd, "--enable"], check=False)
    if r.returncode == 0:
      print_info("Safari WebDriver enabled.")
      return
    print_info(
      f"sudo safaridriver --enable failed (exit {r.returncode}). "
      "Run manually in a terminal: sudo safaridriver --enable"
    )

  def register_tests(self, cli: click.Group) -> None:
    """Attach the ``tests`` command group."""
    app = self

    @cli.group("tests")
    def cli_tests():
      """Run zmsautomation ATAF tests on the Mac host (outside containers)."""
      pass

    @cli_tests.command("install-mac-deps")
    @click.option(
      "--edge-driver-version",
      default=None,
      help=(
        "Microsoft Edge WebDriver version (e.g. 146.0.3856.72). "
        "Default: read from /Applications/Microsoft Edge.app, else a bundled fallback."
      ),
    )
    def cli_tests_install_mac_deps(edge_driver_version):
      """Install Maven, JDK 21 (Homebrew), Chrome/Firefox WebDrivers (Homebrew), Edge WebDriver (Microsoft), enable Safari WebDriver."""
      app.mac_require_darwin()
      brew = shutil.which("brew")
      if not brew:
        raise click.ClickException(
          "Homebrew not found. Install from https://brew.sh then re-run this command."
        )
      pkgs = [
        "maven",
        "openjdk@21",
        "chromedriver",
        "geckodriver",
      ]
      print_info("Installing: " + ", ".join(pkgs))
      app.run_cmd([brew, "install"] + pkgs)

      app.install_msedgedriver_mac(version=edge_driver_version)

      app.enable_safari_webdriver_mac()

      print_info("Suggested ~/.zshrc (adjust if you use bash):")
      print(
        'export PATH="$HOME/.local/bin:$(brew --prefix openjdk@21)/bin:$PATH"\n'
        'export JAVA_HOME="$(brew --prefix openjdk@21)/libexec/openjdk.jdk/Contents/Home"'
      )
      print_info("Then: source ~/.zshrc  (or open a new terminal)")

    @cli_tests.command("trust-local-gateway")
    def cli_tests_trust_local_gateway():
      """Import TLS cert from https://localhost:8091 into ~/.config/eappointment/ (for JVM / mvn)."""
      app.mac_require_darwin()
      java_home = app.resolve_mac_java_home()
      print_info(
        "Building truststore (JDK cacerts copy + gateway cert). "
        f"Password is standard cacerts password: {app.CACERTS_DEFAULT_PASSWORD}"
      )
      p = app.prepare_local_gateway_truststore(java_home)
      print_info(f"Truststore: {p}")

    @cli_tests.command("run-mac-local")
    @click.option(
      "--base-uri",
      default=None,
      help=f"Override BASE_URI (default: {TestCli.RUN_MAC_LOCAL_BASE_URI}).",
    )
    @click.option(
      "--citizen-api-uri",
      default=None,
      help=f"Override CITIZEN_API_BASE_URI (default: {TestCli.RUN_MAC_LOCAL_CITIZEN_API_URI}).",
    )
    @click.option(
      "--citizen-view-uri",
      default=None,
      help=f"Override CITIZEN_VIEW_BASE_URI (default: {TestCli.RUN_MAC_LOCAL_CITIZEN_VIEW_URI}).",
    )
    @click.option(
      "--admin-base-uri",
      default=None,
      help=(
        f"Override ADMIN_BASE_URI for zmsadmin UI tests (default: {TestCli.RUN_MAC_LOCAL_ADMIN_URI}, "
        "or :8080 when --api-http)."
      ),
    )
    @click.option(
      "--statistic-base-uri",
      default=None,
      help=(
        f"Override STATISTIC_BASE_URI (default: {TestCli.RUN_MAC_LOCAL_STATISTIC_URI}, "
        "or :8080 when --api-http)."
      ),
    )
    @click.option(
      "--api-http",
      is_flag=True,
      default=False,
      help="Use http://localhost:8080/... for BASE_URI and CITIZEN_API_BASE_URI instead of https://8091.",
    )
    @click.option(
      "--skip-gateway-trust",
      is_flag=True,
      default=False,
      help="Do not build ~/.config/eappointment/cacerts-with-local-gateway-8091.jks (skip JVM trust import for 8091).",
    )
    @click.option(
      "--browser",
      type=click.Choice(["chrome", "firefox", "edge"], case_sensitive=False),
      default="chrome",
      show_default=True,
    )
    @click.option(
      "--cucumber-tags",
      default="(@rest or @web) and not @ignore",
      show_default=True,
      help="Cucumber tag expression (-Dcucumber.filter.tags).",
    )
    @click.option(
      "--screenshots-every-step",
      is_flag=True,
      default=False,
      help="Enable SCREENSHOT_EVERY_STEP (slower; more artifacts).",
    )
    @click.option(
      "--sso-username",
      default="ataf",
      show_default=True,
      help="Matches testautomation.properties / Keycloak test user for local ATAF.",
    )
    @click.option(
      "--sso-password",
      default="vorschau",
      show_default=True,
      help="Matches testautomation.properties for local ATAF.",
    )
    @click.option(
      "--db-full-setup",
      is_flag=True,
      default=False,
      help="Run ./cli db full-setup first (needs MYSQL_* env pointing at your DB).",
    )
    @click.option(
      "--citizenview-base-url",
      default=None,
      help="Flyway placeholder for db full-setup (default: env CITIZENVIEW_BASE_URI or http://localhost:8082/).",
    )
    @click.option(
      "--skip-driver-check",
      is_flag=True,
      default=False,
      help="Do not fail if WebDrivers for other browsers are missing (only the selected browser is required).",
    )
    def cli_tests_run_mac_local(
      base_uri,
      citizen_api_uri,
      citizen_view_uri,
      admin_base_uri,
      statistic_base_uri,
      api_http,
      skip_gateway_trust,
      browser,
      cucumber_tags,
      screenshots_every_step,
      sso_username,
      sso_password,
      db_full_setup,
      citizenview_base_url,
      skip_driver_check,
    ):
      """Run mvn test -Pataf-ui from zmsautomation/ with JAVA_HOME and WebDrivers."""
      app.mac_require_darwin()
      java_home = app.resolve_mac_java_home()
      wd = app.resolve_webdriver_paths()
      browser = browser.lower()

      required = wd.get(browser)
      if not required:
        raise click.ClickException(
          f"No driver in PATH for browser={browser!r}. "
          f"Run: ./cli tests install-mac-deps"
        )
      if not skip_driver_check:
        missing = [b for b, p in wd.items() if not p]
        if missing:
          print_info(
            "Optional drivers not in PATH (ok if you only use one browser): "
            + ", ".join(missing)
          )

      zmsautomation_dir = os.path.join(app.repo_dir, "zmsautomation")
      cli_exe = os.path.join(app.repo_dir, "cli")

      if db_full_setup:
        if citizenview_base_url:
          flyway_cv = citizenview_base_url
        elif citizen_view_uri:
          flyway_cv = citizen_view_uri.rstrip("/") + "/"
        else:
          flyway_cv = app.RUN_MAC_LOCAL_CITIZEN_VIEW_URI
        print_info(f"Running database full-setup (citizenviewBaseUrl={flyway_cv})...")
        subprocess.run(
          [sys.executable, cli_exe, "db", "full-setup", "--citizenview-base-url", flyway_cv],
          cwd=app.repo_dir,
          check=True,
        )

      env = os.environ.copy()
      env["JAVA_HOME"] = java_home
      env["PATH"] = os.path.join(java_home, "bin") + os.pathsep + env.get("PATH", "")

      if api_http:
        env["BASE_URI"] = "http://localhost:8080/terminvereinbarung/api/2"
        env["CITIZEN_API_BASE_URI"] = "http://localhost:8080/terminvereinbarung/api/citizen"
        env["ADMIN_BASE_URI"] = app.RUN_MAC_LOCAL_ADMIN_URI_HTTP
        env["STATISTIC_BASE_URI"] = app.RUN_MAC_LOCAL_STATISTIC_URI_HTTP
      else:
        env["BASE_URI"] = app.RUN_MAC_LOCAL_BASE_URI
        env["CITIZEN_API_BASE_URI"] = app.RUN_MAC_LOCAL_CITIZEN_API_URI
        env["ADMIN_BASE_URI"] = app.RUN_MAC_LOCAL_ADMIN_URI
        env["STATISTIC_BASE_URI"] = app.RUN_MAC_LOCAL_STATISTIC_URI
      env["CITIZEN_VIEW_BASE_URI"] = app.RUN_MAC_LOCAL_CITIZEN_VIEW_URI
      if base_uri is not None:
        env["BASE_URI"] = base_uri
      if citizen_api_uri is not None:
        env["CITIZEN_API_BASE_URI"] = citizen_api_uri
      if citizen_view_uri is not None:
        env["CITIZEN_VIEW_BASE_URI"] = citizen_view_uri.rstrip("/") + "/"
      if admin_base_uri is not None:
        env["ADMIN_BASE_URI"] = admin_base_uri.rstrip("/") + "/"
      if statistic_base_uri is not None:
        env["STATISTIC_BASE_URI"] = statistic_base_uri.rstrip("/") + "/"

      mvn_args = [
        "mvn",
        "-B",
        "-DtrimStackTrace=false",
        "test",
        "-Pataf-ui",
        f"-Dbrowser={browser}",
        f"-Dtestautomation.browser={browser}",
        f"-Dcucumber.filter.tags={cucumber_tags}",
        f"-Dsso.username={sso_username}",
        f"-Dsso.password={sso_password}",
        f"-DSCREENSHOT_EVERY_STEP={'true' if screenshots_every_step else 'false'}",
      ]
      if wd.get("chrome"):
        mvn_args.append(f"-Dwebdriver.chrome.driver={wd['chrome']}")
      if wd.get("firefox"):
        mvn_args.append(f"-Dwebdriver.gecko.driver={wd['firefox']}")
      if wd.get("edge"):
        mvn_args.append(f"-Dwebdriver.edge.driver={wd['edge']}")

      if browser == "edge":
        mvn_args.extend(
          [
            "-Dtestautomation.platformName=mac",
            "-DplatformName=mac",
          ]
        )

      if app.needs_local_gateway_truststore(env, api_http) and not skip_gateway_trust:
        print_info(
          "JVM trust for https://localhost:8091: importing gateway cert into "
          f"~/.config/eappointment/{app.LOCAL_GATEWAY_TRUSTSTORE_FILENAME} …"
        )
        ts = app.prepare_local_gateway_truststore(java_home)
        mvn_args.extend(
          [
            f"-Djavax.net.ssl.trustStore={ts}",
            f"-Djavax.net.ssl.trustStorePassword={app.CACERTS_DEFAULT_PASSWORD}",
          ]
        )

      print_info(f"JAVA_HOME={java_home}")
      print_info(f"Browser={browser} driver={required}")
      print_info(f"BASE_URI={env['BASE_URI']}")
      print_info(f"CITIZEN_API_BASE_URI={env['CITIZEN_API_BASE_URI']}")
      print_info(f"ADMIN_BASE_URI={env['ADMIN_BASE_URI']}")
      print_info(f"STATISTIC_BASE_URI={env['STATISTIC_BASE_URI']}")
      print_info(f"CITIZEN_VIEW_BASE_URI={env['CITIZEN_VIEW_BASE_URI']}")
      app.run_cmd(mvn_args, cwd=zmsautomation_dir, env=env)
