<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\OtpService;
use App\Support\Turnstile;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Gets the site ready on a server and checks its settings.
 * Safe to run after every deployment: php artisan kidsavon:setup
 */
class SetupCommand extends Command
{
    protected $signature = 'kidsavon:setup {--no-migrate : Skip creating/updating database tables}';

    protected $description = 'Prepare the Kids Avon site on a server (folders, key, database, admin, caches) and check its settings';

    private int $problems = 0;

    public function handle(): int
    {
        $this->info('Kids Avon setup');

        $this->folders();
        $this->appKey();
        $databaseOk = $this->database();

        if ($databaseOk && ! $this->option('no-migrate')) {
            $this->call('migrate', ['--force' => true]);
            if (! User::query()->exists()) {
                $this->call('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);
            }
        }

        // Clear old cached config/routes/views (file caches only; works even without a database).
        foreach (['config:clear', 'route:clear', 'view:clear'] as $command) {
            $this->callSilently($command);
        }
        if ($databaseOk) {
            try {
                $this->callSilently('cache:clear');
            } catch (Throwable) {
                // The cache table may not exist yet; nothing to clear.
            }
        }
        if (app()->isProduction()) {
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');
        }

        $this->checks();

        $this->newLine();
        $this->problems
            ? $this->warn("Finished with {$this->problems} thing(s) to fix (see ✗ above).")
            : $this->info('All good. The site is ready.');

        return self::SUCCESS;
    }

    /** storage, bootstrap/cache and public/uploads must exist and be writable. */
    private function folders(): void
    {
        $dirs = [
            storage_path('app/public'), storage_path('framework/cache/data'), storage_path('framework/sessions'),
            storage_path('framework/views'), storage_path('logs'), base_path('bootstrap/cache'),
            public_path('uploads/rides'), public_path('uploads/avatars'), public_path('uploads/badges'),
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @chmod($dir, 0775);
            $this->line(is_writable($dir) ? "  ✓ writable  {$this->rel($dir)}" : "  ✗ NOT writable  {$this->rel($dir)}");
            if (! is_writable($dir)) {
                $this->problems++;
            }
        }
    }

    private function appKey(): void
    {
        if (filled(config('app.key'))) {
            $this->line('  ✓ APP_KEY is set');

            return;
        }

        if (! is_file(base_path('.env'))) {
            $this->error('  ✗ There is no .env file. Copy .env.production.example to .env and fill it in.');
            $this->problems++;

            return;
        }

        $this->call('key:generate', ['--force' => true]);
    }

    private function database(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->line('  ✓ Database connection works ('.config('database.default').')');

            return true;
        } catch (Throwable $e) {
            $this->error('  ✗ Cannot connect to the database: '.$e->getMessage());
            $this->line('    Check DB_DATABASE, DB_USERNAME and DB_PASSWORD in .env (cPanel → MySQL Databases).');
            $this->problems++;

            return false;
        }
    }

    /** Settings that should be right on the live site. */
    private function checks(): void
    {
        $live = app()->isProduction();
        $checks = [
            ['APP_ENV=production', $live],
            ['APP_DEBUG=false (visitors never see code details)', ! config('app.debug')],
            ['APP_URL starts with https://', str_starts_with((string) config('app.url'), 'https://')],
            ['Cloudflare Turnstile site key + secret key set (not testing keys)', Turnstile::enabled() && filled(config('kidsavon.turnstile.secret_key')) && ! Turnstile::usingTestKeys()],
            ['OTP testing mode off (OTP_TEST_CODE empty)', ! OtpService::testCode()],
            ['Email sending set up (MAIL_MAILER=smtp, MAIL_HOST)', config('mail.default') === 'smtp' && filled(config('mail.mailers.smtp.host')) && ! str_contains((string) config('mail.mailers.smtp.host'), 'example.com')],
            ['MAIL_FROM_ADDRESS set', filled(config('mail.from.address')) && ! str_ends_with((string) config('mail.from.address'), '@example.com')],
        ];

        $this->newLine();
        $this->info('Settings check');
        foreach ($checks as [$label, $ok]) {
            $this->line(($ok ? '  ✓ ' : '  ✗ ').$label);
            if (! $ok && $live) {
                $this->problems++;
            }
        }
    }

    private function rel(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), '/');
    }
}
