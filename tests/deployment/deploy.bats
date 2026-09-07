#!/usr/bin/env bats

load './support/deploy-fixtures'

setup() {
  setup_deploy_fixture
}

@test 'deployment test fixtures initialize' {
  [ -x "$fake_bin/git" ]
  [ -x "$fake_bin/composer" ]
  [ -x "$fake_bin/npm" ]
  [ -x "$fake_bin/php84" ]
  [ -f "$target_base/test/backend/.env" ]
  [ -f "$target_base/prod/backend/.env" ]
}
