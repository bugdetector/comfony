# Claude Code Guidelines for Comfony

Please strictly follow the project rules, architectural guidelines, and execution workflows defined in `AGENTS.md` at the repository root.

- **Main Instructions:** [AGENTS.md](AGENTS.md)
- **CRUD & Component Templates:** [.agents/skills/comfony-crud/SKILL.md](.agents/skills/comfony-crud/SKILL.md)
- **daisyUI 5 Component Guide:** [.agents/skills/daisyui/SKILL.md](.agents/skills/daisyui/SKILL.md)

## Key Commands
- Console (Docker): `./bin/docker-console.sh <command>`
- Console (Local): `./bin/console <command>`
- Apply Schema Changes (NO migrations!): `./bin/docker-console.sh config:import`
- Run Tests: `./bin/phpunit`

