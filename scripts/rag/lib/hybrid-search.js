'use strict';

function tokenize(text) {
  return text
    .toLowerCase()
    .replace(/[^\w\u4e00-\u9fff]/g, ' ')
    .split(/\s+/)
    .filter(t => t.length > 1);
}

function bm25Search(chunks, query, options = {}) {
  const { top = 10, layer = '', module: mod = '' } = options;

  let filtered = chunks;
  if (layer) {
    filtered = filtered.filter(c => c.layer === layer);
  }
  if (mod) {
    filtered = filtered.filter(c => c.module.includes(mod));
  }

  const queryTokens = tokenize(query);
  if (queryTokens.length === 0) return [];

  const N = filtered.length;
  const k1 = 1.5;
  const b = 0.75;

  const docLengths = filtered.map(c => tokenize(c.content).length);
  const avgDl = docLengths.reduce((sum, l) => sum + l, 0) / N || 1;

  const df = {};
  for (const chunk of filtered) {
    const tokens = new Set(tokenize(chunk.content));
    for (const t of tokens) {
      df[t] = (df[t] || 0) + 1;
    }
  }

  const scored = filtered.map((chunk, idx) => {
    const tokens = tokenize(chunk.content);
    const tf = {};
    for (const t of tokens) {
      tf[t] = (tf[t] || 0) + 1;
    }

    let score = 0;
    for (const qt of queryTokens) {
      const termFreq = tf[qt] || 0;
      const docFreq = df[qt] || 0;
      if (termFreq === 0 || docFreq === 0) continue;

      const idf = Math.log((N - docFreq + 0.5) / (docFreq + 0.5) + 1);
      const dl = docLengths[idx];
      const tfNorm = (termFreq * (k1 + 1)) / (termFreq + k1 * (1 - b + b * dl / avgDl));
      score += idf * tfNorm;
    }

    return { chunk, score };
  });

  return scored
    .filter(s => s.score > 0)
    .sort((a, b) => b.score - a.score)
    .slice(0, top)
    .map(s => ({ ...s.chunk, score: Math.round(s.score * 1000) / 1000 }));
}

module.exports = { bm25Search, tokenize };
