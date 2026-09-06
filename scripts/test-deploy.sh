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
if [[ "${1:-}" == 'run' && "${2:-}" == 'build' ]]; then
  mkdir -p dist/frontend/browser
  printf 'new-test-frontend\n' > dist/frontend/browser/index.html
fi
FAKE_NPM

cat > "$fake_bin/rsync" <<'FAKE_RSYNC'
#!/usr/bin/env bash
set -Eeuo pipefail
printf 'rsync %s\n' "$*" >> "${DEPLOY_TEST_LOG}"
/usr/bin/rsync "$@"
FAKE_RSYNC

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

[[ "$(<"$target_base/test/backend/.env")" == $'APP_ENV=test\n' ]]
[[ "$(<"$target_base/test/frontend/index.html")" == $'new-test-frontend\n' ]]
grep -Fq 'git clone --branch main --single-branch https://example.test/repository.git' "$log_file"
grep -Fq 'phinx migrate -e test' "$log_file"

if run_deploy prod 'NO'; then
  printf 'Expected production deployment to require confirmation.\n' >&2
  exit 1
fi
[[ "$(<"$target_base/prod/frontend-placeholder")" == $'old-prod-frontend\n' ]]

run_deploy prod 'DEPLOY PROD'
[[ "$(<"$target_base/prod/backend/.env")" == $'APP_ENV=prod\n' ]]
grep -Fq 'phinx migrate -e production' "$log_file"

printf 'Deployment script tests passed.\n'
