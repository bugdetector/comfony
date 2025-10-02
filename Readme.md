# Comfony - Modern Symfony Boilerplate

Comfony is a comfortable, production-ready Symfony 7.3 boilerplate featuring [daisyUI](https://daisyui.com/) (Tailwind CSS 4), Hotwire Turbo, Stimulus, and UX Live Components for real-time reactive UIs.

---

## 💡 Try with Copilot

Want to see Comfony in action? Try this prompt with GitHub Copilot:

```
Create a Note entity and its CRUD.
```

Copilot will generate a fully integrated Note entity, including real-time updates, LiveComponent forms, search, and daisyUI-powered templates—following all Comfony conventions.

---

## 🚀 Getting Started

### Start the Development Environment

```sh
docker compose up
```

### Watch Assets

```sh
yarn watch
# or
npm run watch
```

---

## 🌐 Translations

- Extract English translations:
  ```sh
  symfony console translation:extract --force --format=yaml en
  ```
- Extract Turkish translations:
  ```sh
  symfony console translation:extract --force --format=yaml tr
  ```

---

## ⏰ Scheduling

- **List scheduled commands:**
  ```sh
  symfony console schedule:list
  ```
- **Run scheduler:**
  ```sh
  symfony console schedule:run
  ```

---

## 🔒 Staging HTTP Authentication

To secure your staging environment with HTTP Basic Auth:

### 1. Generate `.htpasswd`

In your `public` directory:

```sh
htpasswd -c .htpasswd <username>
# Enter your password when prompted
```

### 2. Configure Apache

Add to your `.htaccess`:

```apache
<If "%{ENV:APP_ENV} == 'staging'">
  AuthType Basic
  AuthName "Restricted"
  AuthUserFile <absolute path to .htpasswd>
  Require valid-user
</If>
```

### 3. Configure Nginx

Add to your server block:

```nginx
location / {
  auth_basic "Restricted";
  auth_basic_user_file <absolute path to .htpasswd>;
}
```

Replace `<absolute path to .htpasswd>` with the full path to your `.htpasswd` file.

---

## 🧹 Code Quality

Run the following to fix and check code standards:

```sh
phpcbf && phpcs
```

---

## 🗄️ Database & Migrations

Comfony manages your database schema automatically. **Do not create migration files manually** for structure changes.

Apply all schema changes with:

```sh
symfony console config:import
```

This command wraps `doctrine:migrations:migrate`, `doctrine:schema:update`, and `config:dump-import`. Use migration files only for custom operations.

---

## 📦 Configuration Dump Structure

When using Comfony's configuration dump system, you define your data export/import structure in YAML. Each section describes an entity, which fields to import, filter, and how to handle relations. Here’s how the structure works:

- **configuration**:  
  - `entity`: The fully qualified class name of the entity (e.g., `App\Entity\Main\Configuration\Configuration`).
  - `importFields`: List of fields to import/export (e.g., `configKey`, `value`).
  - `filterFields`: Fields used for filtering during import (e.g., `configKey`).

- **permissions**:  
  - `entity`: The entity class for permissions.
  - `importFields`: Fields to import/export (e.g., `name`, `description`).
  - `filterFields`: Fields used for filtering (e.g., `name`).
  - `relations`: Nested relations, such as translations.
    - `{relation_name}`: (e.g, `translations`)  
      - `entity`: Related entity class (e.g., `App\Entity\Main\PermissionTranslation`).
      - `fields`: Fields to import/export for the relation (e.g., `locale`, `field`, `content`).

This structure allows you to precisely control which data is exported or imported, and how related entities (like translations) are handled. You can extend this pattern for other entities as needed.

---

## 🐳 Symfony Docker

- [Options available](docs/options.md)
- [Using Symfony Docker with an existing project](docs/existing-project.md)
- [Support for extra services](docs/extra-services.md)
- [Deploying in production](docs/production.md)
- [Debugging with Xdebug](docs/xdebug.md)
- [TLS Certificates](docs/tls.md)
- [Using MySQL instead of PostgreSQL](docs/mysql.md)
- [Using Alpine Linux instead of Debian](docs/alpine.md)
- [Using a Makefile](docs/makefile.md)
- [Updating the template](docs/updating.md)
- [Troubleshooting](docs/troubleshooting.md)

---

## 📄 License

Comfony is available under the MIT License.

---

## 🙏 Credits

Created by [Murat Baki Yücel](https://github.com/bugdetector).
