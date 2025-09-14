# Comfony - Comfortable Symfony boilerplate - developed with daisyui, tailwindcss, turbo, ux live components


## Start development enviroment
```sh
docker compose up
```

## Watch assets
```sh
yarn watch
```
OR
```sh
npm run watch
```

## Translations
- Dump English translations:
  - ```sh
    symfony console translation:extract --force --format=yaml en
    ```
- Dump Turkish translations:
  - ```sh
    symfony console translation:extract --force --format=yaml tr
    ```

## List Schedule Commands
```sh
symfony console schedule:list
```
## Run Schedule
```sh
symfony console schedule:run
```
## Staging HTTP Authentication

To secure your staging environment with HTTP Basic Auth, follow these steps:

### 1. Generate `.htpasswd` file

Run the following command in your project's public directory, replacing `<username>` with your desired username:

```sh
htpasswd -c .htpasswd <username>
# Enter your password when prompted
```

### 2. Configure Apache

Add the following to your `.htaccess` file to enable authentication only in the staging environment:

```apache
<If "%{ENV:APP_ENV} == 'staging'">
  AuthType Basic
  AuthName "Restricted"
  AuthUserFile <absolute path to .htpasswd>
  Require valid-user
</If>
```

### 3. Configure Nginx

Add these lines to your Nginx server block to enable authentication:

```nginx
server {
  # ... other config ...

  location / {
    # ... other config ...
    auth_basic "Restricted";
    auth_basic_user_file <absolute path to .htpasswd>;
  }
}
```

Replace `<absolute path to .htpasswd>` with the full path to your `.htpasswd` file.


## Code Quality check
Please run ``` phpcbf ``` then ``` phpcs ``` commands to ensure code visible quality.

## Migrations
All migration files will automaticaly generated and applied with this command.
It is not suggested to generate mogration files while using comfony.

```sh
symfony console config:import
```


# Symfony Docker

## Docs

1. [Options available](docs/options.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using MySQL instead of PostgreSQL](docs/mysql.md)
8. [Using Alpine Linux instead of Debian](docs/alpine.md)
9. [Using a Makefile](docs/makefile.md)
10. [Updating the template](docs/updating.md)
11. [Troubleshooting](docs/troubleshooting.md)

## License

Symfony Docker is available under the MIT License.

## Credits

Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
