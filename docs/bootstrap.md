# Local bootstrap

## Requirements

- PHP 8.2 or newer
- Composer 2
- Node.js 20 or newer
- npm
- PostgreSQL (preferred) or SQLite for the first local run

## Generate the Laravel application

Because this repository already contains the product documentation, generate Laravel in a temporary directory and copy the framework skeleton without replacing `.git` or this documentation.

```bash
cd "/Users/rayanfalcao/Library/Application Support/Herd/config/valet/Sites"
git clone https://github.com/rayanfalcao8/mirev-access.git
composer create-project laravel/laravel mirev-access-skeleton "^12.0"
rsync -av --exclude='.git' --exclude='README.md' mirev-access-skeleton/ mirev-access/
cd mirev-access
rm -rf ../mirev-access-skeleton
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan test
git status --short
```

Review the generated files before committing them.

## Planned first branch

```bash
git switch -c codex/client-foundation
git add .
git commit -m "chore: scaffold Laravel application"
git push -u origin codex/client-foundation
```

Do not add real Mobile Money, RADIUS, router, database, or production credentials to Git.
