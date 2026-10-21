#!/usr/bin/env python3
"""Build an HTML gallery of failed ATAF scenarios from cucumber.json + step screenshots.

Writes:
  <out-dir>/index.html                         — failed shots (large cards)
  <out-dir>/<failures-subdir>/<nn>-<slug>/index.html — all shots for that scenario
                                                 (failed shot first)

Screenshot folders match ScreenshotHook:
  target/screenshots/<module>/<TICKETS>_scenario_name/<yyyyMMdd-HHmmss.SSS>.png
"""

from __future__ import annotations

import argparse
import html
import json
import os
import re
import shutil
import sys
from pathlib import Path
from typing import Any, Iterable

TICKET_TAG = re.compile(r"(?i)^(ZMSKVR|ZMS|GH)-(\d+)$")
MODULE_TAGS = {
    "zmsadmin",
    "zmscitizenview",
    "zmsstatistic",
    "zmsticketprinter",
    "zmscalldisplay",
}

CSS = """
body { font-family: system-ui, sans-serif; margin: 24px; background: #111; color: #eee; }
h1 { font-size: 1.35rem; margin: 0 0 8px; }
h2 { font-size: 1.1rem; margin: 0 0 8px; }
.meta { font-size: 13px; color: #aaa; margin: 0 0 20px; }
.meta code { color: #ccc; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 20px; }
.card { display: block; background: #1c1c1c; border-radius: 8px; padding: 12px; color: inherit;
        text-decoration: none; border: 1px solid #333; }
a.card:hover { outline: 2px solid #4af; border-color: #4af; }
.card img { width: 100%; height: auto; border-radius: 4px; background: #000; margin-bottom: 10px; }
.step { font-size: 13px; color: #9cf; margin: 4px 0 8px; white-space: pre-wrap; }
.err { font-size: 12px; color: #f88; margin: 6px 0 8px; white-space: pre-wrap;
       max-height: 8em; overflow: auto; }
code.path { font-size: 11px; color: #aaa; word-break: break-all; display: block; }
.badge { display: inline-block; font-size: 11px; padding: 2px 8px; border-radius: 999px;
         background: #522; color: #fcc; margin-bottom: 8px; }
.badge.ok { background: #243; color: #9c9; }
.nav { margin: 0 0 16px; }
.nav a { color: #6bf; }
.empty { color: #888; }
.shot-label { font-size: 12px; color: #bbb; margin: 0 0 6px; }
"""


def esc(s: str) -> str:
    return html.escape(s or "", quote=True)


def load_json(path: Path) -> list[Any]:
    if not path.is_file():
        return []
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as e:
        print(f"warn: could not read {path}: {e}", file=sys.stderr)
        return []
    return data if isinstance(data, list) else []


def tag_names(element: dict[str, Any], feature: dict[str, Any]) -> list[str]:
    tags: list[str] = []
    for block in (feature.get("tags"), element.get("tags")):
        if not block:
            continue
        for t in block:
            name = t.get("name") if isinstance(t, dict) else str(t)
            if name:
                tags.append(name if name.startswith("@") else f"@{name}")
    return tags


def ticket_prefix(tags: Iterable[str]) -> str:
    tickets: set[str] = set()
    for raw in tags:
        tag = raw[1:] if raw.startswith("@") else raw
        m = TICKET_TAG.match(tag)
        if m:
            tickets.add(f"{m.group(1).upper()}-{m.group(2)}")
    if not tickets:
        return ""
    return "_".join(sorted(tickets)) + "_"


def scenario_name_part(name: str) -> str:
    return re.sub(r"[^a-zA-Z0-9._\-]+", "_", name or "")


def folder_name_for(name: str, tags: Iterable[str]) -> str:
    return ticket_prefix(tags) + scenario_name_part(name)


def resolve_module(tags: Iterable[str], uri: str) -> str:
    for raw in tags:
        tag = (raw[1:] if raw.startswith("@") else raw).lower()
        if tag in MODULE_TAGS:
            return tag
    norm = uri.replace("\\", "/")
    for m in MODULE_TAGS:
        if f"/ui/{m}/" in norm:
            return m
    return "ui-other"


def failed_step_info(element: dict[str, Any]) -> tuple[str, str]:
    for step in element.get("steps") or []:
        result = step.get("result") or {}
        if result.get("status") != "failed":
            continue
        keyword = (step.get("keyword") or "").strip()
        name = (step.get("name") or "").strip()
        step_text = f"{keyword} {name}".strip()
        err = (result.get("error_message") or "").strip()
        # First line + a bit of context; keep HTML cards readable.
        err_short = err.split("\n", 1)[0]
        if len(err) > len(err_short) + 20:
            err_short = err[:600] + ("…" if len(err) > 600 else "")
        return step_text, err_short
    return "", ""


def find_screenshot_dir(
    screenshots_root: Path, module: str, folder: str, scenario_name: str
) -> Path | None:
    direct = screenshots_root / module / folder
    if direct.is_dir():
        return direct
    # Fallback: unique folder name under any module.
    matches = list(screenshots_root.glob(f"*/{folder}"))
    if len(matches) == 1 and matches[0].is_dir():
        return matches[0]
    # Loose match on scenario name suffix (ticket order / truncation drift).
    suffix = scenario_name_part(scenario_name)
    candidates = [
        p
        for p in screenshots_root.glob("*/*")
        if p.is_dir() and (p.name == folder or p.name.endswith(suffix))
    ]
    if len(candidates) == 1:
        return candidates[0]
    return None


def list_pngs(folder: Path) -> list[Path]:
    return sorted(folder.glob("*.png"), key=lambda p: p.name)


def slugify(name: str) -> str:
    s = re.sub(r"[^a-zA-Z0-9]+", "-", name or "scenario").strip("-").lower()
    return (s[:80] or "scenario")


def rel_href(from_dir: Path, to_path: Path) -> str:
    return os.path.relpath(str(Path(to_path).resolve()), str(Path(from_dir).resolve())).replace(
        "\\", "/"
    )


def page_shell(title: str, body: str, root_href: str | None = None) -> str:
    nav = ""
    if root_href is not None:
        nav = f'<p class="nav"><a href="{esc(root_href)}">← Failed screenshots</a></p>\n'
    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>{esc(title)}</title>
<style>{CSS}</style>
</head>
<body>
{nav}{body}
</body>
</html>
"""


def collect_failures(
    cucumber_paths: list[Path], screenshots_root: Path
) -> list[dict[str, Any]]:
    failures: list[dict[str, Any]] = []
    for cucumber_path in cucumber_paths:
        for feature in load_json(cucumber_path):
            if not isinstance(feature, dict):
                continue
            uri = str(feature.get("uri") or "")
            for element in feature.get("elements") or []:
                if not isinstance(element, dict):
                    continue
                if element.get("type") == "background":
                    continue
                name = element.get("name") or ""
                if not name:
                    continue
                statuses = [
                    (s.get("result") or {}).get("status")
                    for s in (element.get("steps") or [])
                ]
                if "failed" not in statuses:
                    continue
                tags = tag_names(element, feature)
                module = resolve_module(tags, uri)
                folder = folder_name_for(name, tags)
                shot_dir = (
                    find_screenshot_dir(screenshots_root, module, folder, name)
                    if screenshots_root.is_dir()
                    else None
                )
                pngs = list_pngs(shot_dir) if shot_dir else []
                failed_png = pngs[-1] if pngs else None
                step_text, err = failed_step_info(element)
                failures.append(
                    {
                        "name": name,
                        "module": module,
                        "folder": folder,
                        "uri": uri,
                        "cucumber": cucumber_path.name,
                        "step": step_text,
                        "error": err,
                        "shot_dir": shot_dir,
                        "pngs": pngs,
                        "failed_png": failed_png,
                    }
                )
    return failures


def write_gallery(
    failures: list[dict[str, Any]],
    out_dir: Path,
    screenshots_root: Path,
    failures_subdir: str,
    title: str,
    index_name: str = "index.html",
) -> Path:
    out_dir.mkdir(parents=True, exist_ok=True)
    failures_root = out_dir / failures_subdir
    out_resolved = out_dir.resolve()
    failures_resolved = failures_root.resolve()
    if failures_resolved == out_resolved or out_resolved not in failures_resolved.parents:
        raise ValueError(f"--failures-subdir must be a subdirectory of {out_dir}")
    if failures_root.exists():
        shutil.rmtree(failures_root)
    failures_root.mkdir(parents=True, exist_ok=True)
    index_path = out_dir / index_name

    cards: list[str] = []
    for i, fail in enumerate(failures, start=1):
        slug = f"{i:02d}-{slugify(fail['name'])}"
        detail_dir = failures_root / slug
        detail_dir.mkdir(parents=True, exist_ok=True)
        detail_index = detail_dir / "index.html"
        detail_href = f"{failures_subdir}/{slug}/index.html"

        # Detail page: failed shot first, then the rest in time order.
        ordered: list[Path] = []
        if fail["failed_png"] is not None:
            ordered.append(fail["failed_png"])
        for p in fail["pngs"]:
            if fail["failed_png"] is None or p != fail["failed_png"]:
                ordered.append(p)

        detail_cards: list[str] = []
        for j, png in enumerate(ordered):
            img_href = rel_href(detail_dir, png)
            is_failed = j == 0 and fail["failed_png"] is not None
            label = "Failed step (last)" if is_failed else f"Earlier step ({png.name})"
            badge = (
                '<div class="badge">failed</div>'
                if is_failed
                else '<div class="badge ok">earlier</div>'
            )
            detail_cards.append(
                f'<div class="card">{badge}'
                f'<div class="shot-label">{esc(label)}</div>'
                f'<a href="{esc(img_href)}" target="_blank">'
                f'<img src="{esc(img_href)}" alt="{esc(png.name)}"/></a>'
                f'<code class="path">{esc(png.name)}</code></div>'
            )

        detail_body = f"""<h1>{esc(fail["name"])}</h1>
<p class="meta">Module <code>{esc(fail["module"])}</code> · folder <code>{esc(fail["folder"])}</code>
 · {len(ordered)} screenshot(s). Failed shot is first.</p>
<p class="step"><strong>Failed step:</strong> {esc(fail["step"] or "(unknown)")}</p>
<pre class="err">{esc(fail["error"] or "")}</pre>
<div class="grid">
{"".join(detail_cards) if detail_cards else '<p class="empty">No screenshots for this scenario.</p>'}
</div>
"""
        detail_index.write_text(
            page_shell(fail["name"], detail_body, root_href=rel_href(detail_dir, index_path)),
            encoding="utf-8",
        )

        if fail["failed_png"] is not None:
            img_href = rel_href(out_dir, fail["failed_png"])
            img_block = f'<img src="{esc(img_href)}" alt="{esc(fail["name"])}"/>'
            try:
                shown = fail["failed_png"].resolve().relative_to(screenshots_root.resolve()).as_posix()
            except ValueError:
                shown = fail["failed_png"].name
            path_block = f'<code class="path">{esc(shown)}</code>'
        else:
            img_block = '<p class="empty">No screenshot</p>'
            path_block = ""
        cards.append(
            f'<a class="card" href="{esc(detail_href)}">'
            f"{img_block}"
            f'<div><strong>{esc(fail["name"])}</strong>'
            f'<div class="step"><strong>Failed step:</strong> {esc(fail["step"] or "(unknown)")}</div>'
            f'<pre class="err">{esc(fail["error"] or "")}</pre>'
            f"{path_block}"
            f"</div></a>"
        )

    body = f"""<h1>{esc(title)}</h1>
<p class="meta">{len(failures)} failed scenario(s) with screenshots under
<code>{esc(str(screenshots_root))}</code>. Click a card for every step screenshot
(failed shot first).</p>
<div class="grid">
{"".join(cards) if cards else '<p class="empty">No failed scenarios.</p>'}
</div>
"""
    index_path.write_text(page_shell(title, body), encoding="utf-8")
    return index_path


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--cucumber-json",
        action="append",
        default=[],
        help="Path to cucumber.json (repeatable). Default: target/cucumber.json and cucumber-api.json",
    )
    parser.add_argument(
        "--screenshots-dir",
        default="target/screenshots",
        help="Root of per-scenario screenshot folders",
    )
    parser.add_argument(
        "--out-dir",
        default="target/screenshots",
        help="Where to write index.html and failure subpages (default: screenshots dir)",
    )
    parser.add_argument(
        "--failures-subdir",
        default="_failures",
        help="Subdirectory under out-dir for per-scenario galleries",
    )
    parser.add_argument(
        "--title",
        default="ATAF failed screenshots",
        help="H1 title on the index page",
    )
    parser.add_argument(
        "--index-name",
        default="index.html",
        help="Filename for the failure index (default: index.html)",
    )
    args = parser.parse_args()

    screenshots_root = Path(args.screenshots_dir)
    out_dir = Path(args.out_dir)
    cucumber_paths = [Path(p) for p in args.cucumber_json]
    if not cucumber_paths:
        cucumber_paths = [
            Path("target/cucumber.json"),
            Path("target/cucumber-api.json"),
        ]

    existing = [p for p in cucumber_paths if p.is_file()]
    if not existing:
        print("warn: no cucumber.json found; writing empty gallery", file=sys.stderr)

    shot_root = screenshots_root if screenshots_root.is_dir() else out_dir
    failures = collect_failures(existing or cucumber_paths, shot_root)
    index = write_gallery(
        failures,
        out_dir=out_dir,
        screenshots_root=shot_root,
        failures_subdir=args.failures_subdir,
        title=args.title,
        index_name=args.index_name,
    )
    print(f"wrote {index} ({len(failures)} failure(s))")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
