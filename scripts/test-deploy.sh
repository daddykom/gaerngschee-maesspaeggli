#!/usr/bin/env bash

set -Eeuo pipefail

readonly project_directory="$(cd "$(dirname "$0")/.." && pwd)"
readonly test_directory="$(mktemp -d "${TMPDIR:-/tmp}/gaerngschee-deploy-test.XXXXXX")"
readonly fake_bin="$test_directory/bin"
readonly target_base="$test_directory/maesspaeggli"
readonly log_file="$test_directory/commands.log"

cleanup() {
  rm -rf "$test_directory"
}
trap cleanup EXIT

fail() {
  printf 'Deployment script test failed: %s\n' "$1" >&2
  if [[ -f "$log_file" ]]; then
    printf '%s\n' 'Command log:' >&2
    while IFS= read -r line; do
      printf '%s\n' "$line" >&2
    done < "$log_file"
  fi
  exit 1
}

assert_file_contains() {
  local expected="$1"
  local file="$2"
  grep -Fq "$expected" "$file" || {
    fail "expected '$file' to contain '$expected'"
  }
}

assert_log_contains() {
  local expected="$1"
  grep -Fq "$expected" "$log_file" || fail "expected command log to contain '$expected'"
}

mkdir -p "$fake_bin" "$target_base/test/backend" "$target_base/test/frontend" "$target_base/prod/backend"
printf 'APP_ENV=test\n' > "$target_base/test/backend/.env"
printf 'APP_ENV=prod\n' > "$target_base/prod/backend/.env"
printf 'old-test-frontend\n' > "$target_base/test/frontend/index.html"
printf 'old-prod-frontend\n' > "$target_base/prod/frontend-placeholder"

cat > "$fake_bin/git" <<'FAKE_GIT'
#!/usr/bin/env bash
set -Eeuo pipefail
destination="${@: -1}"
printf 'git %s\n' "$*" >> "${DEPLOY_TEST_LOG}"
mkdir -p "$destination/backend" "$destination/frontend" "$destination/db"
FAKE_GIT

cat > "$fake_bin/composer" <<'FAKE_COMPOSER'
#!/usr/bin/env bash
set -Eeuo pipefail
working_directory='.'
for argument in "$@"; do
  if [[ "$argument" == --working-dir=* ]]; then
    working_directory="${argument#--working-dir=}"
  fi
done
mkdir -p "$working_directory/vendor/bin"
cat > "$working_directory/vendor/bin/phinx" <<'FAKE_PHINX'
#!/usr/bin/env bash
set -Eeuo pipefail
printf 'phinx %s\n' "$*" >> "${DEPLOY_TEST_LOG}"
FAKE_PHINX
chmod +x "$working_directory/vendor/bin/phinx"
FAKE_COMPOSER

cat > "$fake_bin/npm" <<'FAKE_NPM'
#!/usr/bin/env bash
set -Eeuo pipefail
printf 'npm %s\n' "$*" >> "${DEPLOY_TEST_LOG}"
if [[ "$*" == *'build'* ]]; then
  mkdir -p dist/frontend/browser
  printf 'new-test-frontend\n' > dist/frontend/browser/index.html
fi
FAKE_NPM

chmod +x "$fake_bin"/*

run_deploy() {
  local environment="$1"
  local input="${2:-}"
  if [[ -n "$input" ]]; then
    printf '%s\n' "$input" | env \
      PATH="$fake_bin:/usr/bin:/bin" \
      DEPLOY_BASE_DIR="$target_base" \
      DEPLOY_TEST_LOG="$log_file" \
      REPO_URL='https://example.test/repository.git' \
      "$project_directory/scripts/deploy.sh" "$environment"
  else
    env \
      PATH="$fake_bin:/usr/bin:/bin" \
      DEPLOY_BASE_DIR="$target_base" \
      DEPLOY_TEST_LOG="$log_file" \
      REPO_URL='https://example.test/repository.git' \
      "$project_directory/scripts/deploy.sh" "$environment"
  fi
}

run_deploy test

assert_file_contains 'APP_ENV=test' "$target_base/test/backend/.env"
assert_file_contains 'new-test-frontend' "$target_base/test/frontend/index.html"
assert_log_contains 'git clone --branch main --single-branch https://example.test/repository.git'
assert_log_contains 'phinx migrate -e test'

if run_deploy prod 'NO'; then
  printf 'Expected production deployment to require confirmation.\n' >&2
  exit 1
fi
assert_file_contains 'old-prod-frontend' "$target_base/prod/frontend-placeholder"

run_deploy prod 'DEPLOY PROD'
assert_file_contains 'APP_ENV=prod' "$target_base/prod/backend/.env"
assert_log_contains 'phinx migrate -e production'

printf 'Deployment script tests passed.\n'
