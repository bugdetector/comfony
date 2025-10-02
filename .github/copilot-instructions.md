# Comfony - Symfony + Hotwire Stack Instructions

Comfony is a modern Symfony 7.3 boilerplate with Hotwire Turbo, Stimulus, daisyUI/TailwindCSS, and UX Live Components for real-time reactive UIs.

## Architecture Overview

**Frontend Stack**: Hotwire Turbo + Stimulus + TailwindCSS + daisyUI
**Backend**: Symfony 7.3 + Doctrine + UX Live Components
**Real-time**: Mercure for broadcasting entity changes
**Theme System**: Extensible theme directory structure with `theme.themeDirectory` variable

## Core Development Patterns

### Entity Development (follows Doctrine + Broadcast patterns)
- Entities may use `TimestampableEntity` and `BlameableEntity` traits if needed.
- Add `#[Broadcast(topics: ['entityname'], private: true)]` for real-time updates if needed.
- Setters MUST accept nullable parameters for LiveComponent compatibility.
- File attachments use Many-to-Many or Many-To-One with `App\Entity\File\File`
- NO manual migrations - run `php bin/console config:import` for schema changes

### CRUD Implementation (LiveComponent + Turbo pattern)
1. **Controller**: Standard Symfony controller with index/new/edit/delete routes
2. **Search Component**: Extends `DatatableComponent` with table config, filters, sorting
3. **LiveForm Component**: Uses `ComponentWithFormTrait` for reactive forms
4. **Templates**: 
   - Index extends base with search component
   - Edit uses LiveForm with `initialFormData`
   - Row templates for table display with actions
5. **Broadcast Stream**: `templates/broadcast/Entity/Entity.stream.html.twig` for real-time updates

### LiveComponent Development
- Place in `App\Twig\Components` namespace
- Search components extend `DatatableComponent` base class
- Form components use `ComponentWithFormTrait`
- Use `LiveAsyncFileInputTrait` if form uses async file input.s
- Define `#[LiveProp(writable: true, url: true)]` for URL persistence
- Use `#[LiveAction]` methods for user interactions

## Key Commands & Workflows

```bash
# Development environment
docker compose up                    # Start full stack
yarn watch                         # Watch assets (or npm run watch)

# Translations  
symfony console translation:extract --force --format=yaml en
symfony console translation:extract --force --format=yaml tr

# Database changes (no migrations!)
php bin/console config:import       # Auto-apply schema changes
php bin/console config:dump         # Dump configuration defiled in config/dump_config.yml

# Code quality
phpcbf && phpcs                    # Fix + check code standards

# Scheduling
symfony console schedule:list       # List scheduled commands
symfony console schedule:run        # Run scheduler
```

## Frontend Architecture
- **Stimulus Controllers**: Located in `assets/controllers/`
- **Build**: Webpack Encore with Tailwind CSS + daisyUI + PostCSS
- **Icons**: Tabler icons via `i-tabler-{name}` classes
- **Modals**: Use `data-controller="modal"` with modal targets
- **Real-time**: Turbo streams automatically update via Mercure broadcasts

## File Organization Conventions
- **Controllers**: `src/Controller/{Feature}/` (e.g., `Admin/PageController.php`)
- **Entities**: `src/Entity/{Domain}/` (e.g., `Page/Page.php`)
- **Components**: `src/Twig/Components/{Feature}SearchComponent.php`
- **Templates**: `templates/{feature}/` with `_row.html.twig`, `_form.html.twig` partials
- **Theme Assets**: `templates/themes/base_theme/` (extensible theme system)

---
applyTo: "**"
---
Apply the [comfony coding instructions](./instructions/comfony-instructions.md) to all code.

---
applyTo: "**/*.html.twig,**/*.twig,**/*.html"
---
Apply the [daisyui coding instructions](./instructions/daisyui-instructions.md) to all files that contains html.