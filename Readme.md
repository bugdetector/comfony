# Comfony - Comfortable Symfony boilerplate - developed with tailwindcss, turbo, ux live components

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