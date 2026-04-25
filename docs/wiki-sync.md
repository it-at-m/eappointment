# GitHub Wiki

## Current status

**Automated wiki sync is disabled.** Keeping the wiki in sync with `docs/` would require a repository secret with push access to [https://github.com/it-at-m/eappointment.wiki.git](https://github.com/it-at-m/eappointment.wiki.git) (typically a PAT). That was deferred to avoid org PAT requests.

The wiki remains available for **manual** edits and links:

- [https://github.com/it-at-m/eappointment/wiki](https://github.com/it-at-m/eappointment/wiki)

## Source of truth

Authoritative documentation for developers lives in this repository under [`docs/`](./index.md) and on [GitHub Pages](https://it-at-m.github.io/eappointment/).

## Optional manual sync

To refresh the wiki from `docs/` without automation:

1. Clone `https://github.com/it-at-m/eappointment.wiki.git`
2. Copy the desired `docs/*.md` files into the wiki repo (rename `docs/index.md` to `Home.md` for the wiki home page if you want that layout).
3. Commit and push.

## Re-enabling automation (later)

If the org approves a credential, a workflow can be added again that uses a secret (for example `WIKI_PUSH_TOKEN`) with `actions/checkout` of the `.wiki` repository and the same copy or `rsync` steps as before.
