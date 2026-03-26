'use strict';

const fs = require('fs');
const path = require('path');
const { REPO_ROOT } = require('./lib/doc-paths');

const CHUNKS_PATH = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-chunks.json');
const OUTPUT = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-vectordb.jsonl');

function exportForVectorDb() {
  if (!fs.existsSync(CHUNKS_PATH)) {
    console.error('✗ Chunks not found. Run: npm run rag:chunks');
    process.exit(1);
  }

  const data = JSON.parse(fs.readFileSync(CHUNKS_PATH, 'utf8'));
  const lines = [];

  for (const chunk of data.chunks) {
    lines.push(JSON.stringify({
      id: chunk.id,
      text: chunk.content,
      metadata: {
        source: chunk.source,
        layer: chunk.layer,
        module: chunk.module,
        heading: chunk.heading,
        start_line: chunk.start_line,
        hash: chunk.hash,
      },
    }));
  }

  fs.writeFileSync(OUTPUT, lines.join('\n') + '\n');
  console.log(`✓ Vector DB export: ${lines.length} entries → ${OUTPUT}`);
  console.log(`  File size: ${(fs.statSync(OUTPUT).size / 1024).toFixed(1)} KB`);
}

exportForVectorDb();
