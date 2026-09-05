import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';

export function issueRegistrationToken(email: string, mode?: 'expired'): string {
  const composeFile = resolve(process.cwd(), '..', 'docker-compose.integration.yml');
  const output = execFileSync(
    'docker',
    [
      'compose',
      '-f',
      composeFile,
      'exec',
      '-T',
      'integration-backend',
      'php',
      '/var/www/html/tests/Support/IssueIntegrationToken.php',
      email,
      ...(mode ? [mode] : []),
    ],
    { encoding: 'utf8' },
  );

  return output.trim();
}
