'use strict';

const path = require('path');

const REPO_ROOT = path.resolve(__dirname, '..', '..', '..');

const EXCLUDED_DIRS = new Set([
  'node_modules',
  'vendor',
  'ai-pipeline',
  'archive',
  'deprecated',
  '.git',
]);

const SOURCE_GLOBS = [
  {
    root: '.cursor/rules',
    layer: 'rules',
    exts: ['.mdc'],
  },
  {
    root: 'knowledge',
    layer: 'business',
    exts: ['.md', '.yaml', '.yml'],
  },
  {
    root: 'docs',
    layer: 'business',
    exts: ['.md'],
  },
  {
    root: 'memory',
    layer: 'business',
    exts: ['.md'],
  },
];

function shouldExcludeDir(dirName) {
  return EXCLUDED_DIRS.has(dirName);
}

function getLayerForFile(relPath) {
  for (const source of SOURCE_GLOBS) {
    if (relPath.startsWith(source.root + '/') || relPath.startsWith(source.root + path.sep)) {
      const ext = path.extname(relPath).toLowerCase();
      if (source.exts.includes(ext)) {
        return source.layer;
      }
    }
  }
  return null;
}

function inferModule(relPath) {
  if (relPath.startsWith('.cursor/rules')) return 'global-rules';
  if (relPath.startsWith('memory/')) return 'memory';

  const parts = relPath.split(path.sep);

  if (relPath.startsWith('knowledge/')) {
    const fileName = parts[parts.length - 1].replace(/\.\w+$/, '');
    return `knowledge/${fileName}`;
  }

  if (relPath.startsWith('docs/')) {
    if (relPath.includes('engineering-rules') || relPath.includes('code-patterns')) {
      return 'engineering';
    }
    if (relPath.includes('对接文档') || relPath.includes('-api')) {
      return 'api-docs';
    }
    return 'docs';
  }

  return 'unknown';
}

function toRepoRelative(absPath) {
  return path.relative(REPO_ROOT, absPath).split(path.sep).join('/');
}

module.exports = {
  REPO_ROOT,
  EXCLUDED_DIRS,
  SOURCE_GLOBS,
  shouldExcludeDir,
  getLayerForFile,
  inferModule,
  toRepoRelative,
};
