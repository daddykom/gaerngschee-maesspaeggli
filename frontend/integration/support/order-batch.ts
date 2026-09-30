import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';

export function runIntegrationOrderBatch(): string {
  return execFileSync(
    'docker',
    [
      'compose',
      '-f',
      resolve(process.cwd(), '..', 'docker-compose.integration.yml'),
      'exec',
      '-T',
      'integration-backend',
      'php',
      '/var/www/html/bin/process-orders.php',
    ],
    { encoding: 'utf8' },
  );
}
