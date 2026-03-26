'use strict';

const path = require('path');
const { spawnSync } = require('child_process');

const REPO_ROOT = path.resolve(__dirname, '..', '..');

const STEPS = [
  { script: 'scripts/rag/build-index-manifest.js', label: 'rag:index' },
  { script: 'scripts/rag/build-chunks.js', label: 'rag:chunks' },
  { script: 'scripts/rag/export-for-vector-db.js', label: 'rag:export' },
  { script: 'scripts/rag/check-quality.js', label: 'rag:check' },
];

function runNodeScript(scriptPath, label) {
  console.log(`\n── ${label} ──`);
  const result = spawnSync('node', [scriptPath], {
    cwd: REPO_ROOT,
    stdio: 'inherit',
    env: process.env,
  });

  if (result.status !== 0) {
    console.error(`\n✗ ${label} failed (exit ${result.status})`);
    process.exit(result.status || 1);
  }
}

function main() {
  console.log('╔══════════════════════════════════╗');
  console.log('║  RAG Pipeline Update · v1.0      ║');
  console.log('╚══════════════════════════════════╝');

  const startTime = Date.now();

  for (const step of STEPS) {
    runNodeScript(path.join(REPO_ROOT, step.script), step.label);
  }

  const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
  console.log(`\n✓ RAG pipeline complete (${elapsed}s)`);
  console.log('  Artifacts: knowledge/ai-pipeline/');
  console.log('  Next: restart Cursor to reload MCP server');
}

main();
