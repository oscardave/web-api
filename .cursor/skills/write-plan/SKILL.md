---
name: write-plan
description: Create detailed, execution-ready implementation plans decomposed into N S-level phases. Each phase is a self-contained unit completable in a single Cursor session with high quality.
---

# Write Plan

## Overview

Produce a complete implementation plan where **every phase is S-level** (≤2 files, single Cursor session). The plan must be executable by `execute-plan` with zero ambiguity — a fresh `/clear` window reading any single phase file must have all context to complete it independently.

This skill is for planning only:

- Do not implement code
- Do not modify production files (except plan artifacts)

## Core Principle: S-Level Atomicity

Every phase in the output plan **must** satisfy the S-level contract:

| Constraint | Threshold | Rationale |
|---|---|---|
| Files modified | ≤ 2 | Fits `ai-plan.sh S` gate |
| Tasks per phase | ≤ 5 | Keeps cognitive load manageable |
| Estimated time | 5–15 min | One focused Cursor session |
| Session isolation | 100% | Fresh window can execute without reading other phases |

**Splitting Rule**: If a phase draft exceeds any threshold, split it. Prefer splitting by concern (data vs rendering vs wiring) rather than by file region.

## Workflow

### Step 1: Contextualize

Inspect only the code areas relevant to the requested change.

Capture:

- Existing patterns to follow
- Constraints and dependencies
- Risks, assumptions, and unknowns

### Step 2: Initialize Plan Artifacts

1. Create: `docs/plans/YYMMDD-HHmm-<plan-slug>/`
2. Create:
   - `SUMMARY.md` — overview, phase index, dependency graph
   - one phase file per S-level unit: `phase-XX-<name>.md`
3. Add `research/` only if needed.

### Step 3: Clarify Requirements

Ask clarifying questions to resolve any ambiguity. Focus on:

- Scope and boundaries
- Success criteria
- Constraints and non-goals
- Priorities and trade-offs

#### Rules:

- If requirements are already clear or come from brainstorm context, skip this step.
- Use `Question Tool` for gathering answers.

### Step 4: Draft Strategy → Decompose into S-Level Phases

Design a phased strategy, then apply the **S-Level Decomposition Protocol**:

#### 4a. Draft logical phases

Group work by concern (data layer → rendering → wiring → new features → verification).

#### 4b. S-Level compliance check (per phase)

For each draft phase, verify:

```
[ ] ≤ 2 files modified
[ ] ≤ 5 tasks
[ ] A fresh Cursor window can complete it without reading other phase files
[ ] No task says "see Phase X" without inlining the needed context
[ ] Clear before/after code for every modification
```

#### 4c. Split oversized phases

If a phase fails any check:

1. **Split by concern**: separate data changes from rendering changes from wiring
2. **Split by file**: if touching 3+ files, group into ≤2-file phases
3. **Split by independence**: isolate tasks that don't depend on each other

#### 4d. Write session context for each phase

Each phase must include a **Session Context** block that gives a fresh executor everything needed:

- Which files to open and read first
- What the current state of those files looks like (key code snippets)
- What prior phases changed (inline the relevant facts, don't just reference phase numbers)

### Step 5: Research (Only if Needed)

Research is optional and proportional to uncertainty.

Preferred order:

1. Existing project docs and code
2. Existing skills and local references
3. External references (only if available)

Document findings in `research/<topic>.md`.

### Step 6: Write Plan Content

- `SUMMARY.md` → follow `references/summary-template.md`
- `phase-XX-<name>.md` → follow `references/phase-template.md`

### Step 7: Final S-Level Audit

Before presenting the plan, run this checklist on **every** phase:

| # | Check | Pass? |
|---|---|---|
| 1 | ≤ 2 files modified | |
| 2 | ≤ 5 tasks | |
| 3 | Session Context block present and sufficient | |
| 4 | Every task has before/after code or exact instructions | |
| 5 | Verification commands are concrete and runnable | |
| 6 | Exit criteria are observable (not subjective) | |
| 7 | No dangling references to other phases | |

Then verify cross-cutting concerns:

- Paths are exact and consistent
- Phase order is logical
- Risks/assumptions are explicit
- Plan is executable without hidden context

Present for user review with options (use `Question Tool`):

- **Confirm**: approve plan for execution
- **Validate**: refine via additional questions

### Step 8: Handoff

When approved:

```
Plan `<path>/SUMMARY.md` is ready.
Use `/clear` then `/execute-plan <path>/SUMMARY.md` to execute.
Each phase is S-level — use a fresh `/clear` window per phase for best results.
```

## Rules

- Never implement code in the same session
- Prefer explicit file paths and concrete commands
- Align with project standards and existing architecture
- Keep plans self-contained and deterministic
- **Every phase must be S-level** — this is non-negotiable
- If a brainstorm session already covered requirements, start from Step 4
- Phase files must be self-contained: a reader should never need to open another phase file to understand what to do
