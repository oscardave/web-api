'use strict';

const fs = require('fs');
const path = require('path');
const { REPO_ROOT } = require('./lib/doc-paths');

const MANIFEST = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-index-manifest.json');
const CHUNKS = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-chunks.json');
const HEALTH_OUTPUT = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-health.json');

function checkQuality() {
  const issues = [];
  const stats = {};

  if (!fs.existsSync(MANIFEST)) {
    console.error('✗ Manifest missing. Run: npm run rag:index');
    process.exit(1);
  }
  const manifest = JSON.parse(fs.readFileSync(MANIFEST, 'utf8'));
  stats.total_files = manifest.total_files;
  stats.files_by_layer = manifest.by_layer;

  let missingFiles = 0;
  for (const f of manifest.files) {
    const absPath = path.join(REPO_ROOT, f.path);
    if (!fs.existsSync(absPath)) {
      issues.push({ severity: 'error', message: `Source file missing: ${f.path}` });
      missingFiles++;
    }
  }
  stats.missing_files = missingFiles;

  if (!fs.existsSync(CHUNKS)) {
    console.error('✗ Chunks missing. Run: npm run rag:chunks');
    process.exit(1);
  }
  const chunksData = JSON.parse(fs.readFileSync(CHUNKS, 'utf8'));
  stats.total_chunks = chunksData.total_chunks;
  stats.chunks_by_layer = chunksData.by_layer;

  let emptyChunks = 0;
  let oversizeChunks = 0;
  let tinyChunks = 0;
  const contentLengths = [];

  for (const chunk of chunksData.chunks) {
    const len = chunk.content.length;
    contentLengths.push(len);

    if (len === 0) {
      emptyChunks++;
      issues.push({ severity: 'error', message: `Empty chunk: ${chunk.id} in ${chunk.source}` });
    }
    if (len > 5000) {
      oversizeChunks++;
      issues.push({ severity: 'warn', message: `Oversize chunk (${len} chars): ${chunk.id} in ${chunk.source}` });
    }
    if (len < 50) {
      tinyChunks++;
      issues.push({ severity: 'info', message: `Tiny chunk (${len} chars): ${chunk.id} in ${chunk.source}` });
    }
  }

  contentLengths.sort((a, b) => a - b);
  stats.chunk_sizes = {
    min: contentLengths[0] || 0,
    max: contentLengths[contentLengths.length - 1] || 0,
    median: contentLengths[Math.floor(contentLengths.length / 2)] || 0,
    avg: Math.round(contentLengths.reduce((a, b) => a + b, 0) / contentLengths.length) || 0,
  };
  stats.empty_chunks = emptyChunks;
  stats.oversize_chunks = oversizeChunks;
  stats.tiny_chunks = tinyChunks;

  const chunksSize = fs.statSync(CHUNKS).size;
  stats.chunks_file_size_kb = Math.round(chunksSize / 1024);
  if (chunksSize > 5 * 1024 * 1024) {
    issues.push({ severity: 'warn', message: `Chunks file exceeds 5MB (${stats.chunks_file_size_kb} KB)` });
  }

  const sourcesInChunks = new Set(chunksData.chunks.map(c => c.source));
  const sourcesInManifest = new Set(manifest.files.map(f => f.path));
  const uncoveredFiles = [...sourcesInManifest].filter(s => !sourcesInChunks.has(s));
  stats.coverage = {
    manifest_files: sourcesInManifest.size,
    chunked_files: sourcesInChunks.size,
    uncovered: uncoveredFiles,
  };

  if (uncoveredFiles.length > 0) {
    issues.push({
      severity: 'warn',
      message: `${uncoveredFiles.length} manifest file(s) produced no chunks`,
    });
  }

  const errorCount = issues.filter(i => i.severity === 'error').length;
  const warnCount = issues.filter(i => i.severity === 'warn').length;
  stats.health_score = Math.max(0, 100 - errorCount * 20 - warnCount * 5);

  const health = {
    generated_at: new Date().toISOString(),
    stats,
    issues,
  };

  fs.writeFileSync(HEALTH_OUTPUT, JSON.stringify(health, null, 2) + '\n');

  console.log(`\n═══ RAG Quality Report ═══\n`);
  console.log(`Files: ${stats.total_files} | Chunks: ${stats.total_chunks}`);
  console.log(`Chunk sizes — min: ${stats.chunk_sizes.min}, max: ${stats.chunk_sizes.max}, avg: ${stats.chunk_sizes.avg}, median: ${stats.chunk_sizes.median}`);
  console.log(`Coverage: ${stats.coverage.chunked_files}/${stats.coverage.manifest_files} files`);
  console.log(`Health score: ${stats.health_score}/100`);

  if (issues.length > 0) {
    console.log(`\nIssues (${issues.length}):`);
    for (const issue of issues) {
      const icon = issue.severity === 'error' ? '✗' : issue.severity === 'warn' ? '⚠' : 'ℹ';
      console.log(`  ${icon} [${issue.severity}] ${issue.message}`);
    }
  } else {
    console.log(`\n✓ No issues found`);
  }

  console.log(`\n→ Report saved to ${HEALTH_OUTPUT}`);

  if (errorCount > 0) {
    process.exit(1);
  }
}

checkQuality();
