#!/usr/bin/env bats

load './support/deploy-fixtures'

setup() {
  setup_deploy_fixture
}

@test 'rejects an invalid environment' {
  run "$project_directory/scripts/deploy.sh" staging

  [ "$status" -eq 2 ]
  [[ "$output" == *'Usage:'* ]]
}

@test 'rejects a deployment without an environment file' {
  rm "$target_base/test/backend/.env"

  run run_deploy test

  [ "$status" -eq 1 ]
  [[ "$output" == *"Missing environment file: $target_base/test/backend/.env"* ]]
  [[ "$(< "$target_base/test/frontend/index.html")" == 'old-test-frontend' ]]
}

@test 'deploys the test environment with the test migration environment' {
  printf 'stale-file\n' > "$target_base/test/frontend/stale.html"

  run run_deploy test

  [ "$status" -eq 0 ]
  [ -f "$target_base/test/frontend/index.html" ]
  [ ! -f "$target_base/test/frontend/stale.html" ]
  [[ "$(< "$target_base/test/backend/.env")" == 'APP_ENV=test' ]]
  [[ "$(< "$log_file")" == *'git clone --branch main --single-branch https://example.test/repository.git'* ]]
  [[ "$(< "$log_file")" == *"composer-pwd $target_base/test"* ]]
  [[ "$(< "$log_file")" == *'php84 '* ]]
  [[ "$(< "$log_file")" == *'npm ci'* ]]
  [[ "$(< "$log_file")" == *'npm run build'* ]]
  [[ "$(< "$log_file")" == *'phinx migrate -e test'* ]]
}

@test 'requires explicit confirmation for production deployment' {
  run run_deploy prod NO

  [ "$status" -eq 1 ]
  [[ "$output" == *'Production deployment cancelled.'* ]]
  [ -f "$target_base/prod/frontend-placeholder" ]
  [ ! -f "$log_file" ]
}

@test 'deploys the production environment with the production migration environment' {
  run run_deploy prod 'DEPLOY PROD'

  [ "$status" -eq 0 ]
  [ -f "$target_base/prod/frontend/index.html" ]
  [[ "$(< "$target_base/prod/backend/.env")" == 'APP_ENV=prod' ]]
  [[ "$(< "$log_file")" == *"composer-pwd $target_base/prod"* ]]
  [[ "$(< "$log_file")" == *'phinx migrate -e production'* ]]
}

@test 'uses a configured deployment branch' {
  export DEPLOY_BRANCH=release
  run run_deploy test
  unset DEPLOY_BRANCH

  [ "$status" -eq 0 ]
  [[ "$(< "$log_file")" == *'git clone --branch release --single-branch'* ]]
}
