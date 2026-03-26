# Implementation Plan: <Title>

> Created: YYYY-MM-DD HH:mm:ss
> Phases: N (all S-level)

## Objective

- What is being built/changed and why.
- Link to brainstorm artifact if applicable.

## Scope

### In scope

### Out of scope

### Already done (if applicable)

## Architecture & Approach

- Design decisions and rationale.
- Constraints and compatibility notes.

## Phase Index

> Every phase is S-level (≤ 2 files, ≤ 5 tasks, completable in a single Cursor session).
> Execute each phase in a fresh `/clear` window for best results.

| # | Phase | Goal | Files | Est |
|---|---|---|---|---|
| 01 | [<name>](phase-01-<name>.md) | <one-line goal> | `file-a`, `file-b` | ~X min |
| 02 | [<name>](phase-02-<name>.md) | <one-line goal> | `file-a` | ~X min |

## Phase Dependencies

```
Phase 01 ──→ Phase 02 ──→ Phase 03
                     ╲
                      ──→ Phase 04 (parallel OK)
```

> Phases without arrows between them can be executed in parallel or in any order.

## Key Changes

| File | Phases that modify it |
|---|---|
| `<path>` | 01, 03 |
| `<path>` | 02, 04 |

## Verification Strategy

- Lint/typecheck/tests/build commands
- Manual checks if needed

## Dependencies

- New packages/tools (if any) with reason

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| <risk> | <mitigation> |

## Execution Instructions

1. Open a fresh Cursor window (or `/clear`).
2. Read the target phase file — it contains all context needed.
3. Execute the tasks in order.
4. Run the phase's verification commands.
5. Confirm all exit criteria pass.
6. `/clear` and proceed to the next phase.
