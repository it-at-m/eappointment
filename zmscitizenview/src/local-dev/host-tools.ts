/**
 * Internal host-page tools. Top-right, opposite the login button.
 * Shown on local dev and deployed non-prod hosts. Magnolia embeds never
 * load this module. Production hides it via shouldShowBuildInfo.
 */
import { citizenviewPageHref, citizenviewRootHref } from "@/utils/jumpinLink";

type ToolLink = { label: string; file: string };

const HOST_TOOLS: ToolLink[] = [
  { label: "Jump-in Link Builder", file: "jumpin-link-builder.html" },
];

const MENU_ITEM_STYLE = [
  "display:block",
  "width:100%",
  "padding:.75rem 1rem",
  "border:0",
  "background:transparent",
  "color:#005a9f",
  "font:inherit",
  "text-align:left",
  "cursor:pointer",
  "white-space:nowrap",
  "text-decoration:none",
  "box-sizing:border-box",
].join(";");

function styleMenuItem(el: HTMLElement): void {
  el.setAttribute("style", MENU_ITEM_STYLE);
  el.addEventListener("mouseenter", () => {
    el.style.background = "#e8f1f8";
  });
  el.addEventListener("mouseleave", () => {
    el.style.background = "transparent";
  });
}

/**
 * muc-icon `settings` path from @muenchen/muc-patternlab-vue (muc-icons.svg #icon-settings).
 * Inlined because host chrome lives outside the webcomponent shadow DOM sprite.
 * Same button markup as the local Mein Bereich button.
 */
function createIcon(paths: string, withLabel: boolean): SVGSVGElement {
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("aria-hidden", "true");
  svg.setAttribute("viewBox", "0 0 32 32");
  svg.setAttribute("class", "icon m-button__icon m-button__icon--before");
  svg.setAttribute(
    "style",
    withLabel
      ? "width:1.5rem;height:1.5rem;flex-shrink:0;margin-right:.75rem;fill:currentColor"
      : "width:1.5rem;height:1.5rem;flex-shrink:0;margin-right:0;fill:currentColor"
  );
  svg.innerHTML = paths;
  return svg;
}

function createGearIcon(): SVGSVGElement {
  return createIcon(
    '<path d="M27.237 17.867l-0.679-0.392c0.069-0.444 0.108-0.955 0.108-1.476s-0.039-1.032-0.115-1.532l0.007 0.056 0.679-0.392c0.803-0.469 1.334-1.328 1.334-2.31 0-0.491-0.133-0.952-0.365-1.347l0.007 0.012-1.333-2.309c-0.471-0.802-1.33-1.332-2.312-1.332-0.49 0-0.948 0.132-1.343 0.361l0.013-0.007-0.683 0.4c-0.736-0.582-1.579-1.081-2.484-1.458l-0.070-0.026v-0.783c0-1.473-1.194-2.667-2.667-2.667v0h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 0.783c-0.976 0.402-1.818 0.899-2.577 1.497l0.023-0.017-0.684-0.4c-0.381-0.221-0.839-0.352-1.327-0.352-0.983 0-1.842 0.529-2.308 1.318l-0.007 0.012-1.333 2.309c-0.225 0.383-0.358 0.843-0.358 1.334 0 0.982 0.531 1.84 1.321 2.303l0.012 0.007 0.679 0.392c-0.069 0.444-0.108 0.955-0.108 1.476s0.039 1.032 0.115 1.532l-0.007-0.056-0.679 0.392c-0.803 0.469-1.334 1.328-1.334 2.31 0 0.491 0.133 0.952 0.365 1.347l-0.007-0.012 1.333 2.309c0.471 0.802 1.33 1.332 2.312 1.332 0.49 0 0.948-0.132 1.343-0.361l-0.013 0.007 0.683-0.4c0.736 0.584 1.579 1.084 2.484 1.463l0.070 0.026v0.783c0 1.473 1.194 2.667 2.667 2.667v0h2.667c1.473 0 2.667-1.194 2.667-2.667v0-0.783c0.976-0.402 1.818-0.899 2.577-1.497l-0.023 0.017 0.684 0.4c0.381 0.222 0.84 0.353 1.328 0.353 0.982 0 1.841-0.53 2.306-1.319l0.007-0.012 1.333-2.309c0.227-0.384 0.36-0.845 0.36-1.338 0-0.983-0.532-1.842-1.324-2.304l-0.013-0.007zM23.771 14.147c0.143 0.556 0.225 1.195 0.225 1.852s-0.082 1.296-0.237 1.905l0.012-0.053-0.228 0.965 2.357 1.361-1.333 2.309-2.359-1.361-0.723 0.68c-0.879 0.835-1.952 1.474-3.143 1.842l-0.057 0.015-0.952 0.284v2.72h-2.667v-2.72l-0.953-0.283c-1.248-0.384-2.321-1.023-3.204-1.86l-0.719-0.677-2.359 1.361-1.333-2.309 2.357-1.361-0.228-0.965c-0.143-0.556-0.225-1.195-0.225-1.852s0.082-1.296 0.237-1.905l-0.012 0.053 0.228-0.965-2.357-1.361 1.333-2.309 2.359 1.361 0.723-0.68c0.879-0.835 1.952-1.474 3.143-1.842l0.057-0.015 0.953-0.283v-2.72h2.667v2.72l0.953 0.283c1.248 0.384 2.321 1.023 3.204 1.86l0.719 0.677 2.359-1.361 1.333 2.309-2.355 1.361z"></path>' +
      '<path d="M16 10.667c-2.946 0-5.333 2.388-5.333 5.333s2.388 5.333 5.333 5.333c2.946 0 5.333-2.388 5.333-5.333v0c0-2.946-2.388-5.333-5.333-5.333v0zM16 18.667c-1.473 0-2.667-1.194-2.667-2.667s1.194-2.667 2.667-2.667c1.473 0 2.667 1.194 2.667 2.667v0c0 1.473-1.194 2.667-2.667 2.667v0z"></path>',
    true
  );
}

/** muc-icon `home` path from muc-icons.svg #icon-home. */
function createHomeIcon(): SVGSVGElement {
  return createIcon(
    '<path d="M30.1866 16.4147L16.9558 3.41472c-0.24512 -0.24448 -0.58368 -0.39552 -0.95744 -0.39552s-0.712 0.15104 -0.95744 0.39584L1.81152 16.415l1.91456 2.096 1.82816 -1.79584v10.8598l-0.00032 0.0256a1.41888 1.41888 0 0 0 1.39072 1.41856h5.57184a1.41888 1.41888 0 0 0 1.39232 -1.41856l-0.00032 -0.02688v0.00128 -7.22272h4.17856v7.22272l-0.00032 0.0256a1.41888 1.41888 0 0 0 1.39072 1.41856h5.57184a1.41888 1.41888 0 0 0 1.39232 -1.41856l-0.00032 -0.02688v0.00128 -10.8598l1.82656 1.79584zM23.6534 26.1306h-2.78528V18.9078l0.00032 -0.0256a1.41888 1.41888 0 0 0 -1.39072 -1.41856H12.5139a1.4176 1.4176 0 0 0 -1.392 1.44544v-0.00128 7.22272h-2.78528V13.9773l7.65984 -7.52544 7.66016 7.52544z"></path>',
    false
  );
}

function currentPageFile(): string {
  const path = window.location.pathname.replace(/\/$/, "");
  const segment = path.split("/").pop() || "";
  if (!segment || segment === "buergeransicht") {
    return "index.html";
  }
  return segment;
}

const HOST_TOOLS_CLEANUP = "__zmsHostToolsCleanup";

export function renderHostTools(): void {
  const previous = (
    window as unknown as Record<string, (() => void) | undefined>
  )[HOST_TOOLS_CLEANUP];
  previous?.();
  document.getElementById("zms-host-tools")?.remove();
  document.getElementById("zms-host-tools-menu")?.remove();
  document.getElementById("zms-host-tools-style")?.remove();

  const style = document.createElement("style");
  style.id = "zms-host-tools-style";
  // MDE draws a white ring outside .m-button:focus. On this pair it shows
  // up as a gap or an extra border, so these buttons keep a flat focus state.
  style.textContent = [
    "#zms-host-tools .m-button:focus,",
    "#zms-host-tools .m-button:focus-visible {",
    "outline:none;",
    "box-shadow:none;",
    "z-index:auto;",
    "}",
  ].join("");
  document.head.appendChild(style);

  const host = document.createElement("div");
  host.id = "zms-host-tools";
  host.setAttribute(
    "style",
    "position:fixed;top:0;right:0;z-index:10000;display:flex;flex-flow:row nowrap;align-items:stretch;justify-content:flex-end;gap:0;margin:0;padding:0;background:#005a9f"
  );

  const button = document.createElement("button");
  button.type = "button";
  button.className = "m-button m-button--primary";
  button.appendChild(createGearIcon());
  button.appendChild(document.createTextNode("Tools"));
  button.setAttribute("aria-haspopup", "true");
  button.setAttribute("aria-expanded", "false");
  button.setAttribute("aria-controls", "zms-host-tools-menu");

  const menu = document.createElement("div");
  menu.id = "zms-host-tools-menu";
  menu.setAttribute("role", "menu");
  menu.hidden = true;
  menu.setAttribute(
    "style",
    [
      "position:fixed",
      "margin:0",
      "padding:0",
      "width:max-content",
      "min-width:16rem",
      "background:#fff",
      "border:1px solid #005a9f",
      "box-shadow:0 2px 8px rgba(0,0,0,.15)",
      "z-index:10001",
    ].join(";")
  );

  const activeFile = currentPageFile();
  for (const tool of HOST_TOOLS) {
    const item = document.createElement("a");
    item.setAttribute("role", "menuitem");
    item.href = citizenviewPageHref(window.location, tool.file);
    item.textContent = tool.label;
    styleMenuItem(item);
    if (tool.file === activeFile) {
      item.setAttribute("aria-current", "page");
      item.style.fontWeight = "700";
      item.style.background = "#e8f1f8";
    }
    menu.appendChild(item);
  }

  const placeMenu = (): void => {
    const rect = button.getBoundingClientRect();
    menu.style.top = `${rect.bottom}px`;
    menu.style.left = "auto";
    menu.style.right = `${Math.max(0, document.documentElement.clientWidth - rect.right)}px`;
  };

  const setOpen = (open: boolean): void => {
    if (open) {
      placeMenu();
    }
    menu.hidden = !open;
    button.setAttribute("aria-expanded", open ? "true" : "false");
  };

  button.addEventListener("click", (event) => {
    event.stopPropagation();
    setOpen(menu.hidden);
  });

  const onDocClick = (event: MouseEvent): void => {
    const target = event.target as Node;
    if (!host.contains(target) && !menu.contains(target)) {
      setOpen(false);
    }
  };
  const controller = new AbortController();
  document.addEventListener("click", onDocClick, { signal: controller.signal });
  document.addEventListener(
    "keydown",
    (event) => {
      if (event.key === "Escape") {
        setOpen(false);
      }
    },
    { signal: controller.signal }
  );
  window.addEventListener(
    "resize",
    () => {
      if (!menu.hidden) {
        placeMenu();
      }
    },
    { signal: controller.signal }
  );
  (window as unknown as Record<string, () => void>)[HOST_TOOLS_CLEANUP] =
    () => {
      controller.abort();
    };

  const home = document.createElement("button");
  home.type = "button";
  home.className = "m-button m-button--primary";
  home.setAttribute("aria-label", "Startseite");
  home.style.margin = "0";
  home.style.boxSizing = "border-box";
  // Overlap the shared edge so a fractional pixel cannot show the page behind.
  home.style.marginLeft = "-1px";
  button.style.margin = "0";
  button.style.boxSizing = "border-box";
  button.style.flex = "0 0 auto";
  home.style.flex = "0 0 auto";
  home.appendChild(createHomeIcon());
  home.addEventListener("click", () => {
    window.location.assign(citizenviewRootHref(window.location));
  });

  host.appendChild(button);
  host.appendChild(home);
  document.body.appendChild(host);
  document.body.appendChild(menu);

  // Wide screens give text buttons an explicit height that an icon-only
  // control does not inherit. Match Tools so the pair stays one bar.
  window.requestAnimationFrame(() => {
    const buttonStyle = getComputedStyle(button);
    host.style.backgroundColor = buttonStyle.backgroundColor;
    if (buttonStyle.height && buttonStyle.height !== "auto") {
      home.style.height = buttonStyle.height;
      home.style.minHeight = buttonStyle.height;
    }
  });
}
