# SIMPLE API

> INDONESIA POSTAL CODE & AREA

This contains database postal code & area.

@see [https://github.com/pentagonal/Indonesia-Postal-Code](https://github.com/pentagonal/Indonesia-Postal-Code)

## REQUIREMENT

- php 7.0 or later
- php pdo & sqlite extension
- php json extension
- Web server nginx or apache with mod_rewrite enabled

## COMPOSER DEPENDENCY

- Slim Framework 
    - version: ^3.21 
    - repository: [https://github.com/slimphp/Slim](https://github.com/slimphp/Slim)
    - website: [http://slimframework.com/](http://slimframework.com/)

```json
{
  "slim/slim": "^3.12",
  "pentagonal/database-dbal": "^1"
}
```

## APPLICATION

see [ROUTES.md](ROUTES.md)

Set server environment (to get via `getenv()`) to 'DEVELOPMENT_MODE=1'


## STRUCTURES

Document root placed on `public` directory

```text
(root)/
    ├── app/                    (path of application container, middleware and routes file)
    │    ├── Container.php      (File for Container / Dependency Injection)
    │    ├── Middleware.php     (File for Middleware)
    │    └── Routes.php         (File For Routes)
    │
    ├── public/                 (Path of document root)
    │    └── index.php          (File index root)
    │
    └─── storage/                (Directory to stored data storage)
          └── database/                     (Directory to store database files)       
            └── sqlite_provinces.sqlite     (Provinces & Postal Code database file)

```

## AUTHOR

- nawa
    - email: me@arrayiterator.com
    - website: https://www.pentagonal.org
    - github: [@ArrayIterator](https://github.com/arrayiterator/)


## PURPOSE FOR

- ONPHPID
    - website : [https://www.onphpid.com](https://www.onphpid.com)
    - youtube : [https://www.youtube.com/c/onphpidtutorial](https://www.youtube.com/c/onphpidtutorial)

## LICENSE

[GPL v3 or later](LICENSE)
