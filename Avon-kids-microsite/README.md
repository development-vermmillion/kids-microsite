# Avon Kids Microsite (Kids Avon)

Laravel 13 app for the Avon Cycles kids' riding microsite. The rider-facing site
(frontend) and the admin panel (backend) live in this one app and share one
database.

## Setup

Requires PHP 8.3+ and Composer.

```bash
cd Avon-kids-microsite
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # or set DB_* in .env for MySQL
php artisan migrate --seed            # creates tables + the demo content
php artisan serve                     # http://localhost:8000
```

`--seed` fills the database with the placeholder content from the original HTML
design (riders, rides, badges, challenges, notifications, FAQs) so every page
renders. To reset: `php artisan migrate:fresh --seed`.

## Where things are

| | Frontend (riders) | Backend (admin) |
|---|---|---|
| Routes | `routes/frontend.php` (URLs from `/`) | `routes/backend.php` (URLs under `/admin`, names `admin.*`) |
| Controllers | `app/Http/Controllers/Frontend` | `app/Http/Controllers/Backend` |
| Views | `resources/views/frontend` | `resources/views/backend` |
| CSS / JS / images | `public/frontend` | `public/backend` |

Shared: models in `app/Models`, migrations in `database/migrations`,
site config in `config/kidsavon.php`.

### Frontend pages

| URL | Route name | View | Original file |
|---|---|---|---|
| `/` | `home` | `pages/home` | index.html |
| `/login` | `login` | `pages/login` | login.html |
| `/upload-ride` | `rides.create` | `pages/upload-ride` | upload-ride.html |
| `/challenges` | `challenges.index` | `pages/challenges` | active-challenges.html |
| `/progress` | `progress` | `pages/progress` | progress.html |
| `/trophies` | `trophies` | `pages/trophies` | my-trophies.html |
| `/notifications` | `notifications` | `pages/notifications` | notifications.html |

Old `*.html` URLs redirect to the new ones. All pages share
`frontend/layouts/app.blade.php` with the header and footer partials; the login
page uses `frontend/layouts/base.blade.php` (no header/footer).

The original static HTML is kept for reference in `docs/original-html`
(design files in `docs/design`).

### Database

| Table | Holds |
|---|---|
| `riders` | Kids using the site (name, mobile, avatar, level, OTP fields) |
| `rides` | Uploaded rides — date, time, km, duration, proof image, status `pending / verified / rejected` |
| `challenges` + `challenge_rider` | Challenges and each rider's progress on the ones they accepted |
| `badges` + `badge_rider` | Badges/trophies; per rider: unlock date or progress % |
| `rider_notifications` | Alerts shown on the notifications page |
| `faqs` | Home page FAQ section |
| `settings` | Site-wide values: community miles counter, community goal, support email |
| `users` | Reserved for admin accounts |

Numbers on the pages are calculated from this data: leaderboard = verified km
per rider, "Next Badge" = the locked badge with the highest progress, and so on.

## Not built yet

- **Mobile + OTP login.** Pages are shown for a demo rider ("Alex Rider",
  mobile from `DEMO_RIDER_MOBILE`, default `9999999999`). When login stores
  `session('rider_id')`, every page switches to that rider automatically (see
  `app/Support/CurrentRider.php`).
- **Saving uploaded rides.** The upload form displays but doesn't save yet;
  `RideController` has a TODO for the `store` action.
- **Accept challenge / Log progress** buttons, Settings link, "View All History".
- **Admin panel.** Goes in the Backend folders listed above.
