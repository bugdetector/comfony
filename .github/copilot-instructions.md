# Comfony - Agent & Copilot Instructions

Comfony is a modern Symfony 7.3 boilerplate with Hotwire Turbo, Stimulus, daisyUI 5, Tailwind CSS 4, and Symfony UX Live Components.

## Core Rules & Single Source of Truth
Please strictly follow the complete architectural standards and commands defined in [AGENTS.md](../AGENTS.md):

- **Console execution:** In Docker use `./bin/docker-console.sh <command>`; locally use `./bin/console <command>`.
- **Database changes:** NEVER create manual migrations (`make:migration`). Run `./bin/docker-console.sh config:import`.
- **Entities:** Setters must accept nullable parameters for UX LiveComponent compatibility.
- **Real-time updates:** Use `#[Broadcast(topics: ['...'], private: true)]` for Mercure streaming.

## Specialized Skills & Templates
- **CRUD & Component Generation:** Follow [.agents/skills/comfony-crud/SKILL.md](../.agents/skills/comfony-crud/SKILL.md) for DatatableComponent, LiveForms, and Turbo Stream templates.
- **daisyUI 5 Components:** Follow [.agents/skills/daisyui/SKILL.md](../.agents/skills/daisyui/SKILL.md).