'use strict';

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { REPO_ROOT } = require('./lib/doc-paths');

const MANIFEST = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-index-manifest.json');
const OUTPUT = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-chunks.json');

const MAX_CHUNK_LINES = 80;
const MIN_CHUNK_LINES = 5;

function splitByHeadings(content) {
  const lines = content.split('\n');
  const sections = [];
  let current = { heading: null, startLine: 1, lines: [] };

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const headingMatch = line.match(/^(#{1,3})\s+(.+)$/);

    if (headingMatch && current.lines.length > 0) {
      sections.push(current);
      current = {
        heading: headingMatch[2].trim(),
        headingLevel: headingMatch[1].length,
        startLine: i + 1,
        lines: [line],
      };
    } else {
      current.lines.push(line);
    }
  }

  if (current.lines.length > 0) {
    sections.push(current);
  }

  return sections;
}

function chunkSection(section) {
  if (section.lines.length <= MAX_CHUNK_LINES) {
    return [section];
  }

  const chunks = [];
  let start = 0;
  while (start < section.lines.length) {
    const end = Math.min(start + MAX_CHUNK_LINES, section.lines.length);
    chunks.push({
      heading: section.heading,
      startLine: section.startLine + start,
      lines: section.lines.slice(start, end),
    });
    start = end;
  }
  return chunks;
}

function buildChunks() {
  if (!fs.existsSync(MANIFEST)) {
    console.error('✗ Manifest not found. Run: npm run rag:index');
    process.exit(1);
  }

  const manifest = JSON.parse(fs.readFileSync(MANIFEST, 'utf8'));
  const allChunks = [];
  let chunkId = 0;

  for (const fileEntry of manifest.files) {
    const absPath = path.join(REPO_ROOT, fileEntry.path);
    if (!fs.existsSync(absPath)) {
      console.warn(`⚠ Skipping missing file: ${fileEntry.path}`);
      continue;
    }

    const content = fs.readFileSync(absPath, 'utf8');
    const sections = splitByHeadings(content);

    for (const section of sections) {
      const subChunks = chunkSection(section);
      for (const chunk of subChunks) {
        const text = chunk.lines.join('\n').trim();
        if (text.length < 20 || chunk.lines.length < MIN_CHUNK_LINES) continue;

        chunkId++;
        allChunks.push({
          id: `chunk-${String(chunkId).padStart(4, '0')}`,
          source: fileEntry.path,
          layer: fileEntry.layer,
          module: fileEntry.module,
          heading: chunk.heading || fileEntry.title,
          start_line: chunk.startLine,
          content: text,
          hash: crypto.createHash('sha256').update(text).digest('hex').slice(0, 12),
        });
      }
    }
  }

  const output = {
    generated_at: new Date().toISOString(),
    total_chunks: allChunks.length,
    by_layer: {},
    chunks: allChunks,
  };

  for (const c of allChunks) {
    output.by_layer[c.layer] = (output.by_layer[c.layer] || 0) + 1;
  }

  fs.writeFileSync(OUTPUT, JSON.stringify(output, null, 2) + '\n');
  console.log(`✓ Chunks: ${allChunks.length} chunks → ${OUTPUT}`);
  for (const [layer, count] of Object.entries(output.by_layer)) {
    console.log(`  ${layer}: ${count}`);
  }
}

buildChunks();
