# AGENT.md - MyVetPaws Laravel Application

## Commands
- **Test**: `php artisan test` (all tests), `php artisan test --filter=TestClassName` (single test)
- **Build**: `npm run build` (frontend), `composer install` (backend)
- **Dev**: `composer dev` (runs server, queue, logs, vite concurrently)
- **Lint**: `./vendor/bin/pint` (Laravel Pint for PHP formatting)
- **Database**: `php artisan migrate` (run migrations), `php artisan migrate:fresh --seed` (reset & seed)

## Architecture
- **Laravel 12** application with **Filament 3.3** admin panel
- **Database**: SQLite (test), configured via .env (production)
- **Frontend**: Vite + TailwindCSS 4.0
- **Key Models**: Clinic, Pet, OwnerPet, MedicalRecordService, Product, Subscription
- **Structure**: app/Models/ (Eloquent models), app/Filament/ (admin resources), app/Http/ (controllers)

## Code Style
- **PSR-4** autoloading: App\\ namespace maps to app/
- **Eloquent models**: Use HasFactory trait, protected $fillable arrays, $casts for type casting
- **Filament resources**: Located in app/Filament/Resources/
- **Tests**: Feature tests in tests/Feature/, Unit tests in tests/Unit/
- **Formatting**: Laravel Pint for PHP code formatting
- **Naming**: PascalCase for classes, camelCase for methods/properties, snake_case for database columns
