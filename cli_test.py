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

  RUN_MAC_LOCAL_BASE_URI = "http://localhost:8090/terminvereinbarung/api/2"
  RUN_MAC_LOCAL_CITIZEN_API_URI = "http://localhost:8090/terminvereinbarung/api/citizen"
  RUN_MAC_LOCAL_ADMIN_URI = "http://localhost:8090/terminvereinbarung/admin/"
  RUN_MAC_LOCAL_STATISTIC_URI = "http://localhost:8090/terminvereinbarung/statistic/"
  RUN_MAC_LOCAL_ADMIN_URI_HTTP = "http://localhost:8080/terminvereinbarung/admin/"
  RUN_MAC_LOCAL_STATISTIC_URI_HTTP = "http://localhost:8080/terminvereinbarung/statistic/"
  RUN_MAC_LOCAL_CITIZEN_VIEW_URI = "http://localhost:8082/"

  DEFAULT_MSEDGE_DRIVER_VERSION = "146.0.3856.72"

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

  @staticmethod
  def mac_chrome_bin():
    return "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"

  @staticmethod
  def _first_major_version(text: str) -> int | None:
    m = re.search(r"(?:^|\D)(\d{2,3})\.", text)
    if m:
      return int(m.group(1))
    return None

  @classmethod
  def mac_chrome_major_version(cls) -> int | None:
    """Major version of the installed Google Chrome, or None if not found."""
    p = cls.mac_chrome_bin()
    if not os.path.isfile(p):
      return None
    try:
      r = subprocess.run(
        [p, "--version"],
        capture_output=True,
        text=True,
        timeout=10,
        check=False,
      )
    except (OSError, subprocess.SubprocessError):
      return None
    return cls._first_major_version((r.stdout or "") + (r.stderr or ""))

  @staticmethod
  def mac_chromedriver_major_support(chromedriver_path: str) -> int | None:
    """Major Chrome version the given chromedriver supports (per --version), or None."""
    if not chromedriver_path or not os.path.isfile(chromedriver_path):
      return None
    try:
      r = subprocess.run(
        [chromedriver_path, "--version"],
        capture_output=True,
        text=True,
        timeout=10,
        check=False,
      )
    except (OSError, subprocess.SubprocessError):
      return None
    text = (r.stdout or "") + (r.stderr or "")
    m = re.search(
      r"supports Chrome version (\d+)",
      text,
      re.IGNORECASE,
    ) or re.search(
      r"ChromeDriver\s+(\d+)",
      text,
    )
    if m:
      return int(m.group(1))
    return TestCli._first_major_version(text)

  @classmethod
  def mac_chrome_and_chromedriver_mismatch(cls, chromedriver_path: str) -> bool:
    """True when installed Chrome major version != PATH chromedriver (typical after brew upgrade chromedriver)."""
    cmaj = cls.mac_chrome_major_version()
    dmaj = cls.mac_chromedriver_major_support(chromedriver_path)
    if cmaj is None or dmaj is None:
      return False
    return cmaj != dmaj

  @staticmethod
  def path_without_directories_containing_chromedriver(path_str: str) -> str:
    """Remove PATH entries where an executable `chromedriver` exists (so Selenium Manager is not bound to it)."""
    kept: list[str] = []
    for p in path_str.split(os.path.pathsep):
      if not p:
        continue
      c = os.path.join(p, "chromedriver")
      if os.path.isfile(c) and os.access(c, os.X_OK):
        continue
      kept.append(p)
    return os.path.pathsep.join(kept)

  @staticmethod
  def brew_maven_mvn() -> str | None:
    """`brew --prefix maven` + /bin/mvn, if that layout exists (usually no `chromedriver` next to mvn)."""
    brew = shutil.which("brew")
    if not brew or sys.platform != "darwin":
      return None
    try:
      pfx = subprocess.run(
        [brew, "--prefix", "maven"],
        capture_output=True,
        text=True,
        check=True,
        timeout=20,
      ).stdout.strip()
    except (subprocess.CalledProcessError, OSError, subprocess.SubprocessError, subprocess.TimeoutExpired):
      return None
    m = os.path.join(pfx, "bin", "mvn")
    if os.path.isfile(m) and os.access(m, os.X_OK):
      return m
    return None

  @staticmethod
  def mac_default_browser_path(browser: str) -> str | None:
    """Path to the browser binary if the typical macOS .app install exists, else None."""
    b = browser.lower()
    paths = {
      "chrome": TestCli.mac_chrome_bin(),
      "edge": "/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge",
      "safari": "/Applications/Safari.app/Contents/MacOS/Safari",
      "firefox": "/Applications/Firefox.app/Contents/MacOS/firefox",
    }
    p = paths.get(b)
    if p and os.path.isfile(p):
      return p
    if b == "firefox" and shutil.which("firefox"):
      return shutil.which("firefox")
    return None

  def resolve_webdriver_paths(self):
    candidates = {
      "chrome": ("chromedriver", ["chromedriver"]),
      "firefox": ("geckodriver", ["geckodriver"]),
      "edge": ("msedgedriver", ["msedgedriver", "edgedriver"]),
      "safari": ("safaridriver", ["safaridriver"]),
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
      "--browser",
      type=click.Choice(["chrome", "firefox", "edge", "safari"], case_sensitive=False),
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
    @click.option(
      "--use-selenium-manager",
      is_flag=True,
      default=False,
      help=(
        "Force Selenium Manager (omit -Dwebdriver.*.driver, SELENIUM_MANAGER_DISABLE=false). "
        "By default, this is also enabled automatically when Google Chrome and PATH chromedriver "
        "major versions differ (e.g. after brew upgrade chromedriver)."
      ),
    )
    @click.option(
      "--no-selenium-manager",
      is_flag=True,
      default=False,
      help="Always use WebDrivers from PATH; do not auto-switch to Selenium Manager on version skew.",
    )
    def cli_tests_run_mac_local(
      base_uri,
      citizen_api_uri,
      citizen_view_uri,
      admin_base_uri,
      statistic_base_uri,
      api_http,
      browser,
      cucumber_tags,
      screenshots_every_step,
      sso_username,
      sso_password,
      db_full_setup,
      citizenview_base_url,
      skip_driver_check,
      use_selenium_manager,
      no_selenium_manager,
    ):
      """Run mvn test -Pataf-ui from zmsautomation/ with JAVA_HOME and WebDrivers."""
      app.mac_require_darwin()
      if use_selenium_manager and no_selenium_manager:
        raise click.ClickException("Choose at most one of --use-selenium-manager and --no-selenium-manager.")
      java_home = app.resolve_mac_java_home()
      wd = app.resolve_webdriver_paths()
      browser = browser.lower()

      auto_selenium_manager = (
        not no_selenium_manager
        and not use_selenium_manager
        and browser == "chrome"
        and bool(wd.get("chrome"))
        and app.mac_chrome_and_chromedriver_mismatch(wd["chrome"])
      )
      if auto_selenium_manager:
        print_info(
          "Chrome and chromedriver report different major versions; using Selenium Manager. "
          "Tip: `brew upgrade --cask google-chrome` to align with Homebrew chromedriver, or pass "
          "--no-selenium-manager to keep using PATH chromedriver."
        )
        use_selenium_manager = True

      if use_selenium_manager:
        browser_path = app.mac_default_browser_path(browser)
        if not browser_path:
          raise click.ClickException(
            f"Browser {browser!r} not found in the usual install location. "
            "Install it or run without --use-selenium-manager and put the matching WebDriver in PATH."
          )
        required = f"selenium-manager ({browser_path})"
      else:
        required = wd.get(browser)
        if not required:
          raise click.ClickException(
            f"No driver in PATH for browser={browser!r}. "
            f"Run: ./cli tests install-mac-deps, "
            f"or re-run with --use-selenium-manager if Chrome and Homebrew chromedriver versions differ."
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
      jbin = os.path.join(java_home, "bin")
      mvn_executable = "mvn"
      if use_selenium_manager:
        env["SELENIUM_MANAGER_DISABLE"] = "false"
      if use_selenium_manager and browser == "chrome":
        p_clean = app.path_without_directories_containing_chromedriver(
          os.environ.get("PATH", "")
        )
        brew_mvn = app.brew_maven_mvn()
        if brew_mvn:
          m_dir = os.path.dirname(brew_mvn)
          env["PATH"] = f"{jbin}{os.path.pathsep}{m_dir}{os.path.pathsep}{p_clean}"
          mvn_executable = brew_mvn
        else:
          env["PATH"] = f"{jbin}{os.path.pathsep}{p_clean}"
          w = shutil.which("mvn", path=env["PATH"])
          if w:
            mvn_executable = w
          else:
            raise click.ClickException(
              "Selenium Manager must not see a wrong `chromedriver` in PATH (e.g. /opt/homebrew/bin), "
              "so it can download one that matches your Chrome. After cleaning PATH, `mvn` was not found. "
              "Fix: `brew install maven` (maven’s prefix has no chromedriver), or remove/rename "
              "`/opt/homebrew/bin/chromedriver`, or `brew upgrade --cask google-chrome` to match Homebrew’s driver."
            )
        print_info(
          "Selenium: PATH entries that contain a `chromedriver` binary were removed for the test JVM "
          f"(maven={mvn_executable!r}) so Selenium Manager can resolve the correct driver for installed Chrome."
        )
      else:
        env["PATH"] = f"{jbin}{os.path.pathsep}{env.get('PATH', '')}"

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
        mvn_executable,
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
      if not use_selenium_manager:
        if wd.get("chrome"):
          mvn_args.append(f"-Dwebdriver.chrome.driver={wd['chrome']}")
        if wd.get("firefox"):
          mvn_args.append(f"-Dwebdriver.gecko.driver={wd['firefox']}")
        if wd.get("edge"):
          mvn_args.append(f"-Dwebdriver.edge.driver={wd['edge']}")
        if wd.get("safari"):
          mvn_args.append(f"-Dwebdriver.safari.driver={wd['safari']}")
      else:
        print_info("WebDrivers: Selenium Manager (no -Dwebdriver.*.driver); SELENIUM_MANAGER_DISABLE=false")

      if browser in ("edge", "safari"):
        mvn_args.extend(
          [
            "-Dtestautomation.platformName=mac",
            "-DplatformName=mac",
          ]
        )

      if browser == "safari":
        print_info(
          "Safari: enable Develop → Allow Remote Automation (Safari menu). "
          "Also run ./cli tests install-mac-deps (sudo safaridriver --enable) if you have not."
        )

      print_info(f"JAVA_HOME={java_home}")
      print_info(f"Browser={browser} driver={required}")
      print_info(f"BASE_URI={env['BASE_URI']}")
      print_info(f"CITIZEN_API_BASE_URI={env['CITIZEN_API_BASE_URI']}")
      print_info(f"ADMIN_BASE_URI={env['ADMIN_BASE_URI']}")
      print_info(f"STATISTIC_BASE_URI={env['STATISTIC_BASE_URI']}")
      print_info(f"CITIZEN_VIEW_BASE_URI={env['CITIZEN_VIEW_BASE_URI']}")
      app.run_cmd(mvn_args, cwd=zmsautomation_dir, env=env)
