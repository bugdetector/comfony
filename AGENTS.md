# Comfony - Agent Guidelines & Architecture Manual

Welcome to **Comfony**. This document is the single source of truth (SSoT) for AI agents and developers working in this codebase.

---

## 1. Tech Stack Overview

- **Backend:** PHP 8.4, Symfony 7.3, Doctrine ORM
- **Server / Runtime:** FrankenPHP (Caddy) in Docker
- **Real-Time Streaming:** Mercure protocol with Turbo Streams
- **Frontend / Reactivity:** Hotwire Turbo, Stimulus, Symfony UX Live Components
- **UI & Styling:** Tailwind CSS 4 + daisyUI 5
- **Icons:** Tabler Icons via `i-tabler-{iconName}` utility classes

---

## 2. Command Execution & Console Rules

Always determine the runtime context before running console commands:

- **Docker Environment:**
  Use the dynamic container wrapper script:
  ```bash
  ./bin/docker-console.sh <command>
  # Example:
  ./bin/docker-console.sh config:import
  ./bin/docker-console.sh cache:clear
  ```
- **Host / Local Environment:**
  Use the standard console script:
  ```bash
  ./bin/console <command>
  ```
- **Running Tests:**
  ```bash
  ./bin/phpunit
  ```
- **Translations:**
  ```bash
  ./bin/docker-console.sh translation:extract --force --format=yaml en
  ./bin/docker-console.sh translation:extract --force --format=yaml tr
  ```
- **External Skills Management (via skills-lock.json):**
  ```bash
  npx skills experimental_install   # Restore/install skills from skills-lock.json
  npx skills check                  # Check for upstream skill updates
  npx skills update                 # Update skills to latest upstream versions
  ```

---

## 3. Core Architectural Rules & Invariants

### 1. Database Changes (NO Manual Migrations)
- **NEVER create or edit manual migration files** (`doctrine:migrations:diff`, `make:migration`).
- Comfony manages database schema changes automatically via configuration import.
- Whenever entities are created or changed, run:
  ```bash
  ./bin/docker-console.sh config:import
  # or ./bin/console config:import
  ```

### 2. Entity Conventions
- **Setters MUST accept nullable parameters:** Because Symfony UX LiveComponent binds form states asynchronously, all entity property setters must accept `?Type` (e.g. `public function setTitle(?string $title): static`).
- **Real-time broadcast:** Entities supporting real-time streaming must declare `#[Broadcast(topics: ['entity_plural'], private: true)]`.
- **File attachments:** Relations with uploaded files must link to `App\Entity\File\File` via `ManyToMany` (or `ManyToOne`). Never modify `File` entity unless necessary.
- **Gedmo Traits:** Entities should use `TimestampableEntity` and `BlameableEntity` where applicable.

### 3. Reactive CRUD Pattern
Comfony does not use traditional synchronous CRUD controllers. Instead, it follows a reactive pattern:
1. **Controller (`src/Controller/Admin/`):** Thin controller rendering index, new, edit, and handling delete.
2. **Search Component (`src/Twig/Components/`):** Extends `DatatableComponent` with sort, quick filters, and query builder.
3. **Form Component (`src/Twig/Components/LiveForms/`):** LiveComponent utilizing `ComponentWithFormTrait`, `DefaultActionTrait`, and `LiveAsyncFileInputTrait`.
4. **Turbo Streams (`templates/broadcast/`):** Turbo stream templates (`create`, `update`, `remove`) updating datatable rows automatically via Mercure.

---

## 4. UI & Styling (daisyUI 5)

- Follow **daisyUI 5** component class conventions (e.g. `btn`, `btn-outline`, `btn-soft`, `badge`, `modal`, `drawer`).
- Use responsive Tailwind utility prefixes (`md:`, `lg:`).
- For icons, use Tabler icons classes: `i-tabler-{name}` (e.g., `i-tabler-plus`, `i-tabler-edit`, `i-tabler-trash`).
- Do NOT write custom CSS if a daisyUI component or Tailwind utility class exists.

---

## 5. Modular Agent Skills

Detailed implementation guides and specialized capabilities are organized as modular skills under `.agents/skills/`:

### Internal Project Skills
- **Comfony CRUD & Component Templates:** See [`.agents/skills/comfony-crud/SKILL.md`](.agents/skills/comfony-crud/SKILL.md) for complete Entity, Controller, DatatableComponent, LiveFormComponent, Twig partials, and Turbo Stream code examples.

### External Skills (`skills-lock.json`)
- **UI & Styling Component Documentation:** See [`.agents/skills/daisyui/SKILL.md`](.agents/skills/daisyui/SKILL.md) for daisyUI 5 component, theme, and color documentation.
- External skills are tracked and version-pinned in `skills-lock.json` (similar to `package-lock.json` or `composer.lock`).
- To install/restore them on a fresh clone, run `npx skills experimental_install`.
- To check or update to newer upstream versions, run `npx skills check` or `npx skills update`.

