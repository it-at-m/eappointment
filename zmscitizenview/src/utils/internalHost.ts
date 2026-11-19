/**
 * Internal ZMS citizenview hosts.
 *
 * Prod serves the same pages on zms.muenchen.de (internal) and
 * www48.muenchen.de (public). Magnolia embeds the web component on
 * stadt.muenchen.de. Same split as ACCESS_UNPUBLISHED_ON_DOMAIN=zms:
 * the internal host contains "zms", the public hosts do not.
 * Localhost and ddev count as internal.
 */
const INTERNAL_ZMS_HOST =
  /^(?:localhost|127\.0\.0\.1|\[::1\]|zms\.ddev\.site|zms(?:-[a-z0-9-]+)?\.muenchen\.de)$/;

export function isInternalZmsHost(hostname: string | undefined): boolean {
  const host = (hostname ?? "").trim().toLowerCase().replace(/\.$/, "");
  return INTERNAL_ZMS_HOST.test(host);
}
