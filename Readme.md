# Comfony - Comfortable Symfony boilerplate - developed with tailwindcss, turbo, ux live components

## Submodules
Because of some theme modules has licence restrictions they are not shared as public. To get it please contact me directly for access.

Init submodule
```
git submodule init
```

Update submodule
```
git submodule update --remote
```

## Start development enviroment
```
symfony serve
```

## Watch assets
```
yarn watch
```
OR
```
npm run watch
```

## Translations
- Dump English translations:
  - ```
    symfony console translation:extract --force --format=yaml en
    ```
- Dump Turkish translations:
  - ```
    symfony console translation:extract --force --format=yaml tr
    ```

## List Schedule Commands
```
symfony console schedule:list
```
## Run Schedule
```
symfony console schedule:run
```

## Code Quality check
Please run ``` phpcbf ``` then ``` phpcs ``` commands to ensure code visible quality.

## Migrations
All migration files will automaticaly generated and applied with this command.
It is not suggested to generate mogration files while using comfony.

```
symfony console config:import
```