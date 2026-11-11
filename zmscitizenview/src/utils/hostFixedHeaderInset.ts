/**
 * How far a sticky element inside the widget must sit below the viewport top
 * so a fixed host-page bar (muenchen.de menu) does not cover it.
 * Light-DOM CSS is invisible from the shadow root, so this is measured instead.
 *
 * Do not use position: sticky with this value. muenchen.de wraps the page in
 * .site { overflow: hidden }, and that element becomes the sticky scrollport,
 * so a sticky banner scrolls away with the page. Pin with position: fixed at
 * this inset, and release the pin when the widget scrolls out.
 *
 * Reuse this for ZMSKVR-1525 (Terminstatus in Schritt "Übersicht" und
 * Termin-Detailseite anzeigen): that ticket shows a banner when the appointment
 * is being called in the waiting room, and it has the same sticky offset.
 */
export function hostFixedHeaderInsetPx(): number {
  if (typeof document.elementsFromPoint !== "function") return 0;

  const x = Math.max(0, Math.floor(window.innerWidth / 2));
  let inset = 0;

  for (const el of document.elementsFromPoint(x, 1)) {
    if (el === document.documentElement || el === document.body) continue;
    if (el.getRootNode() instanceof ShadowRoot) continue;

    const style = getComputedStyle(el);
    if (style.position !== "fixed" && style.position !== "sticky") continue;

    const rect = el.getBoundingClientRect();
    if (rect.width < window.innerWidth * 0.5 || rect.height < 1) continue;
    if (rect.top > 1 || rect.bottom > 200) continue;

    inset = Math.max(inset, rect.bottom);
  }

  return Math.max(0, Math.round(inset));
}
