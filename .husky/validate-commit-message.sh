# Shared commit message validation for Husky hooks.
# Usage: . "$(dirname "$0")/validate-commit-message.sh"
#        validate_commit_message "feat(ZMS-123): summary"

validate_commit_message() {
  commit_msg=$1

  RED='\033[0;31m'
  GREEN='\033[0;32m'
  NC='\033[0m'

  if [ -f .git/MERGE_HEAD ] || echo "$commit_msg" | grep -qE '^Merge '; then
    printf "${GREEN}✓ Merge commit message accepted.${NC}\n"
    return 0
  fi

  # One or more type(scope): prefixes, then a summary.
  # Scope may list multiple PROJECT or PROJECT-123 refs separated by spaces.
  # Examples:
  #   feat(ZMS-1): summary
  #   feat(ZMS-1 ZMS-2): summary
  #   feat(ZMS-1 ZMS-2): fix(GH-1 ZMS-3): summary
  valid_types="feat|fix|clean|chore|docs"
  valid_projects="ZMS|ZMSKVR|MPDZBS|MUXDBS|GH"
  ref="($valid_projects)(-[0-9]+)?"
  scope="$ref( $ref)*"
  prefix="($valid_types)\\($scope\\): "
  pattern="^($prefix)+.+"

  if echo "$commit_msg" | grep -qE "$pattern"; then
    printf "${GREEN}✓ Commit message format is valid.${NC}\n"
    return 0
  fi

  printf "${RED}✗ Invalid commit message format!${NC}\n"
  echo ""
  echo "Commit messages must follow the conventional commits format:"
  echo "  type(PROJECT-123): commit message"
  echo "  type(PROJECT): commit message"
  echo "  type(PROJECT-1 PROJECT-2): commit message"
  echo "  type(PROJECT-1 PROJECT-2): type(PROJECT-3): commit message"
  echo ""
  echo "Valid types: feat, fix, clean, chore, docs"
  echo "Valid projects: ZMS, ZMSKVR, MPDZBS, MUXDBS, GH (must be uppercase)"
  echo "Ticket number is optional (e.g., PROJECT-123 or just PROJECT)"
  echo "Multiple tickets/projects may be space-separated in one scope"
  echo "Multiple type(scope): prefixes may be chained before the summary"
  echo ""
  echo "Examples:"
  echo "  feat(ZMS-123): add new feature"
  echo "  fix(ZMSKVR-123): fix bug in login"
  echo "  feat(ZMS-1 ZMS-2): shared change for two tickets"
  echo "  feat(ZMS-1 ZMS-2): fix(GH-1 ZMS-3): multi-type multi-ticket change"
  echo "  chore(GH): clean up"
  echo "  clean(ZMS): remove unused code"
  echo "  docs(ZMS-123): update README"
  echo ""
  echo "For more information, see:"
  echo "  docs/en/setup-and-development/git-hooks.md"
  echo "  https://www.conventionalcommits.org/en/v1.0.0/"
  echo ""
  echo "Your commit message:"
  echo "  $commit_msg"
  return 1
}
