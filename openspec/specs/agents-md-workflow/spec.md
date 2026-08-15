# AGENTS.md Workflow Spec

## Overview

AGENTS.md is the primary context file for AI assistants working on the project. This spec defines its structure and the workflow commands available.

## OpenSpec Commands

### /opsx-explore

Enter explore mode - a thinking partner for exploring ideas, investigating problems, and clarifying requirements.

**Usage:** `/opsx-explore [optional topic]`

When no topic is provided, enters explore mode to discuss freely.

### /opsx-propose

Create a new change proposal with all artifacts generated in one step.

**Usage:** `/opsx-propose [change-name]`

Creates a change in `openspec/changes/<change-name>/` with:
- `proposal.md` - What & why
- `design.md` - How
- `specs/**/*.md` - Detailed specifications
- `tasks.md` - Implementation steps

### /opsx-apply

Implement tasks from an OpenSpec change.

**Usage:** `/opsx-apply [change-name]`

Reads the change's tasks and guides through implementation. Mark tasks complete as you go.

### /opsx-archive

Archive a completed change after implementation is done.

**Usage:** `/opsx-archive [change-name]`

Moves the change from `openspec/changes/<name>/` to `openspec/changes/archive/<name>/`.

### /opsx-sync-specs

Sync delta specs from a change to main specs without archiving.

**Usage:** `/opsx-sync-specs [change-name]`

Updates `openspec/specs/` with changes from the delta specs in a change.

## AGENTS.md Structure

AGENTS.md serves as an index document that links to:

1. **Human-readable documentation** in `documents/`:
   - `documents/project.md` - Project overview
   - `documents/directory-structure.md` - Directory layout
   - `documents/architecture.md` - Architecture decisions
   - `documents/frontend-conventions.md` - Angular patterns
   - `documents/backend-conventions.md` - PHP patterns

2. **Agent-specific specs** in `openspec/specs/`:
   - `openspec/specs/offers/spec.md` - Offer capability
   - `openspec/specs/categories/spec.md` - Category capability
   - `openspec/specs/map/spec.md` - Map capability
   - `openspec/specs/moderation/spec.md` - Moderation capability
   - `openspec/specs/authentication/spec.md` - Authentication capability
   - `openspec/specs/platform/spec.md` - Platform capability

## Requirements

### Requirement: AGENTS.md is an index document
AGENTS.md SHALL serve as an index that links to relevant documentation files rather than containing full content.

### Requirement: OpenSpec commands are documented
AGENTS.md SHALL document all available OpenSpec commands with their purposes and usage patterns.

### Requirement: Human docs link from AGENTS.md
AGENTS.md SHALL contain links to all files in the `documents/` directory.

### Requirement: Specs link from AGENTS.md
AGENTS.md SHALL contain links to capability specs in `openspec/specs/`.

### Requirement: View/Container pattern documented
AGENTS.md SHALL reference `documents/frontend-conventions.md` for the View/Container component pattern.

### Requirement: No duplicate content
AGENTS.md SHALL NOT duplicate content from `documents/` or `openspec/specs/` files.