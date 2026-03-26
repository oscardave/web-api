import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { z } from 'zod';
import { readFileSync, existsSync, appendFileSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import { createRequire } from 'module';

const __dirname = dirname(fileURLToPath(import.meta.url));
const REPO_ROOT = resolve(__dirname, '..', '..');
const require = createRequire(import.meta.url);

const { bm25Search } = require('../rag/lib/hybrid-search.js');

const CHUNKS_PATH = resolve(REPO_ROOT, 'knowledge/ai-pipeline/rag-chunks.json');
const RAG_LOG_CALLS = process.env.RAG_LOG_CALLS === '1';
const LOG_PATH = resolve(REPO_ROOT, '.cursor/rag-calls.log');

function logCall(tool, params, resultCount) {
  if (!RAG_LOG_CALLS) return;
  const ts = new Date().toISOString();
  const line = `${ts}\t${tool}\t${JSON.stringify(params)}\t${resultCount}\n`;
  try { appendFileSync(LOG_PATH, line); } catch {}
}

function loadChunks() {
  if (!existsSync(CHUNKS_PATH)) return null;
  const data = JSON.parse(readFileSync(CHUNKS_PATH, 'utf8'));
  return data;
}

const server = new McpServer({ name: 'rag', version: '1.0.0' });

server.tool(
  'query_evidence',
  {
    q: z.string().describe('Search query'),
    top: z.number().optional().default(10).describe('Max results'),
    layer: z.enum(['rules', 'business', '']).optional().default('').describe('Filter by layer'),
    module: z.string().optional().default('').describe('Filter by module (substring match)'),
  },
  async ({ q, top, layer, module: mod }) => {
    const data = loadChunks();
    if (!data) {
      return {
        content: [{
          type: 'text',
          text: '✗ RAG chunks not found. Run: npm run rag:update',
        }],
      };
    }

    const results = bm25Search(data.chunks, q, { top, layer, module: mod });
    logCall('query_evidence', { q, top, layer, module: mod }, results.length);

    if (results.length === 0) {
      return {
        content: [{
          type: 'text',
          text: `No results for "${q}"${layer ? ` in layer=${layer}` : ''}${mod ? ` module=${mod}` : ''}`,
        }],
      };
    }

    const evidence = results.map((r, i) => {
      const header = `### [${i + 1}] ${r.heading} (${r.source}:${r.start_line})`;
      const meta = `> layer: ${r.layer} | module: ${r.module} | score: ${r.score}`;
      return `${header}\n${meta}\n\n${r.content}`;
    }).join('\n\n---\n\n');

    return {
      content: [{
        type: 'text',
        text: `Found ${results.length} result(s) for "${q}":\n\n${evidence}`,
      }],
    };
  }
);

server.tool(
  'rag_stats',
  {},
  async () => {
    const data = loadChunks();
    if (!data) {
      return {
        content: [{
          type: 'text',
          text: '✗ RAG chunks not found. Run: npm run rag:update',
        }],
      };
    }

    const modules = {};
    for (const c of data.chunks) {
      modules[c.module] = (modules[c.module] || 0) + 1;
    }

    const topModules = Object.entries(modules)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 10)
      .map(([m, count]) => `  ${m}: ${count}`)
      .join('\n');

    const stats = [
      `Total chunks: ${data.total_chunks}`,
      `Generated at: ${data.generated_at}`,
      `By layer: ${JSON.stringify(data.by_layer)}`,
      `\nTop modules:\n${topModules}`,
    ].join('\n');

    logCall('rag_stats', {}, data.total_chunks);

    return {
      content: [{ type: 'text', text: stats }],
    };
  }
);

const transport = new StdioServerTransport();
await server.connect(transport);
