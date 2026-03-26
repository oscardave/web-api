'use strict';

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const {
  REPO_ROOT,
  SOURCE_GLOBS,
  shouldExcludeDir,
  getLayerForFile,
  inferModule,
  toRepoRelative,
} = require('./lib/doc-paths');

const OUTPUT = path.join(REPO_ROOT, 'knowledge/ai-pipeline/rag-index-manifest.json');

function walkDir(dirPath, results) {
  if (!fs.existsSync(dirPath)) return;
  const entries = fs.readdirSync(dirPath, { withFileTypes: true });
  for (const entry of entries) {
    if (entry.isDirectory()) {
      if (!shouldExcludeDir(entry.name)) {
        walkDir(path.join(dirPath, entry.name), results);
      }
    } else if (entry.isFile()) {
      results.push(path.join(dirPath, entry.name));
    }
  }
}

function getFirstHeading(content) {
  const match = content.match(/^#{1,3}\s+(.+)$/m);
  return match ? match[1].trim() : null;
}

function buildManifest() {
  const files = [];

  for (const source of SOURCE_GLOBS) {
    const dirPath = path.join(REPO_ROOT, source.root);
    const found = [];
    walkDir(dirPath, found);

    for (const absPath of found) {
      const ext = path.extname(absPath).toLowerCase();
      if (!source.exts.includes(ext)) continue;

      const relPath = toRepoRelative(absPath);
      const layer = getLayerForFile(relPath);
      if (!layer) continue;

      const content = fs.readFileSync(absPath, 'utf8');
      const stat = fs.statSync(absPath);
      const hash = crypto.createHash('sha256').update(content).digest('hex').slice(0, 12);

      files.push({
        path: relPath,
        layer,
        module: inferModule(relPath),
        title: getFirstHeading(content) || path.basename(relPath, ext),
        size: stat.size,
        lines: content.split('\n').length,
        hash,
        modified: stat.mtime.toISOString(),
      });
    }
  }

  files.sort((a, b) => a.path.localeCompare(b.path));

  const manifest = {
    generated_at: new Date().toISOString(),
    total_files: files.length,
    by_layer: {},
    files,
  };

  for (const f of files) {
    manifest.by_layer[f.layer] = (manifest.by_layer[f.layer] || 0) + 1;
  }

  fs.mkdirSync(path.dirname(OUTPUT), { recursive: true });
  fs.writeFileSync(OUTPUT, JSON.stringify(manifest, null, 2) + '\n');
  console.log(`✓ Index manifest: ${files.length} files → ${OUTPUT}`);
  for (const [layer, count] of Object.entries(manifest.by_layer)) {
    console.log(`  ${layer}: ${count}`);
  }
}

buildManifest();
