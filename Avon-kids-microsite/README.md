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

Admin panel: **http://localhost:8000/admin**, sign in with `admin@kidsavon.com` /
`password` (or set `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` before seeding).
Change the password straight away under **Admin users**.

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
| `users` | Admin accounts |

Numbers on the pages are calculated from this data: leaderboard = verified km
per rider, "Next Badge" = the locked badge with the highest progress, and so on.

## Admin panel (`/admin`)

| Section | What you can do |
|---|---|
| Dashboard | Rides waiting for review, rider count, verified km, active challenges, Hall of Fame |
| Ride review | Filter pending / verified / rejected, search, view the photo proof, **Verify** or **Reject with a reason** (the rider gets an alert either way, and you jump to the next ride in the queue), add/edit/delete rides |
| Riders | Add/edit/delete riders (name, mobile, level, photo, active), see their rides and stats, unlock badges or set badge progress (new badges send an alert), join them to challenges and set progress |
| Challenges | Add/edit/delete challenges: title, description, goal + unit, reward points, icon, colour, dates, order, live/hidden |
| Badges & trophies | Add/edit/delete badges, upload the home page badge image, choose home page and/or Trophy Room |
| Notifications | Send an alert to all riders or one rider; see whether it has been seen |
| FAQs | Edit the home page FAQ section |
| Site settings | Community Miles counter, community goal and progress, support email |
| Admin users | Add admins, change passwords |

Uploaded images are saved in `public/uploads` (not in git); back that folder
up together with the database.

Icons are [Material Symbols](https://fonts.google.com/icons) names
(e.g. `directions_bike`); the forms show a live preview.

## Not built yet

- **Mobile + OTP login.** Pages are shown for a demo rider ("Alex Rider",
  mobile from `DEMO_RIDER_MOBILE`, default `9999999999`). When login stores
  `session('rider_id')`, every page switches to that rider automatically (see
  `app/Support/CurrentRider.php`).
- **Saving uploaded rides.** The upload form displays but doesn't save yet;
  `RideController` has a TODO for the `store` action.
- **Accept challenge / Log progress** buttons, Settings link, "View All History".
