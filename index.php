<?php

/**
 * Web root redirect only.
 *
 * Apache mod_rewrite with [R] builds an absolute Location from Host, which behind
 * GitHub Codespaces is often localhost while the browser uses the forwarded URL.
 * A path-absolute Location is resolved against the current origin (RFC 7231), so
 * this works for local HTTP, Codespaces, and plain port forwarding.
 */
declare(strict_types=1);

header('Location: /terminvereinbarung/admin/', true, 302);
exit;
