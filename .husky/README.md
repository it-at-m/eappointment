# Git Hooks

This directory contains Git hooks managed by [Husky](https://github.com/typicode/husky). These hooks run automatically at specific points in the Git workflow to ensure code quality and consistency.

## Setup

Hooks are automatically set up when you run:

```bash
cd zmscitizenview
npm run prepare
```

This configures Git to use the `.husky` directory for hooks.

> [!NOTE]
> Husky is installed via `zmscitizenview` (since it's a Node.js package), but the hooks in this directory apply to the **entire monorepo**. This centralizes hook management at the repository root while leveraging the Node.js tooling from `zmscitizenview`.

## Hooks

### `pre-commit`

Runs before each commit to validate code quality.

**Checks:**
1. **Vue Code Style** - Runs Prettier on `zmscitizenview` to check code formatting
2. **PHP Code Style** - Runs PHP CodeSniffer (PSR-12) on all PHP modules

**Container Detection:**
The PHP check automatically detects your container environment:
- **Podman** - Detects if `zms-web` container is running via Podman
- **Docker** - Falls back to Docker if Podman is not available
- **Skip** - If no container is running, the PHP check is skipped with a warning

**Behavior:**
- If the `zms-web` container is not running, PHP checks are skipped (non-blocking)
- Vue checks always run and will block the commit if they fail

### `commit-msg`

Validates commit message format to ensure consistency across the repository.

**Format Required:**
```txt
type(PROJECT-123): commit message
type(PROJECT): commit message
```

The ticket number is **optional** - you can use either `PROJECT-123` or just `PROJECT`.

**Valid Types:**
- `feat` - New features
- `fix` - Bug fixes
- `clean` - Code cleanup/refactoring
- `chore` - Maintenance tasks
- `docs` - Documentation updates

**Valid Projects (must be uppercase):**
- `ZMS` - ZMS project
- `ZMSKVR` - ZMSKVR project
- `MPDZBS` - MPDZBS project
- `MUXDBS` - MUXDBS project

**Examples:**
```txt
feat(ZMS-123): add new feature
fix(ZMSKVR-456): fix bug in login
chore(ZMSKVR): clean up
clean(ZMS): remove unused code
docs(ZMS-654): update README
```

## Troubleshooting

### Vue Lint Fails

If Prettier finds formatting issues:

```bash
cd zmscitizenview
npm run format
```

Then commit again.

### PHP CodeSniffer Fails

If PHP CodeSniffer finds errors, the hook will show you the fix command. Run it with your detected container engine:

```bash
# If using Podman
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"

# If using Docker
docker exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"
```

### Container Not Detected

If you see a warning that `zms-web` container is not running:

1. Make sure your container is running:
   ```bash
   # Check Podman
   podman ps | grep zms-web

   # Check Docker
   docker ps | grep zms-web
   ```

2. Start your container if needed (see project README for setup instructions)

3. The PHP check will be skipped automatically if the container isn't running (this won't block your commit)

### Invalid Commit Message Format

The commit message must follow the format `type(PROJECT-123): message` or `type(PROJECT): message`.

**Common mistakes:**
- Missing project identifier: `feat: add feature` ❌
- Lowercase project: `feat(zms-123): add feature` ❌
- Missing colon: `feat(ZMS-123) add feature` ❌

**Correct format:**
- `feat(ZMS-123): add feature` ✓ (with ticket number)
- `chore(ZMSKVR): clean up` ✓ (without ticket number)

## Bypassing Hooks

In emergency situations, you can bypass hooks using Git's `--no-verify` flag:

```bash
# Skip pre-commit hook
git commit --no-verify -m "feat(ZMS-123): urgent fix"

# Skip commit-msg hook
git commit --no-verify -m "feat(ZMS-123): urgent fix"
```

> [!CAUTION]
> Only use `--no-verify` when absolutely necessary. Bypassing hooks can lead to inconsistent code quality and may cause issues for other developers.

## Related Documentation

- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
- [Husky Documentation](https://github.com/typicode/husky)
- Main project [README.md](../README.md)