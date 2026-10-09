# Avon Kids Microsite (Kids Avon)

Laravel 12 app for the Avon Cycles kids' riding microsite. The rider-facing site
(frontend) and the admin panel (backend) live in this one app and share one
database.

## Setup

Requires PHP 8.2+ and Composer.

```bash
cd kids-microsite
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
| `riders` | Kids using the site (name, username, email + when it was verified, mobile, avatar, level) |
| `otp_codes` | Email codes waiting to be used (only a hash of each code is stored) |
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

## How progress works

Only **verified** rides count. Progress updates automatically whenever a ride is
uploaded, verified, rejected, edited or deleted, and when a challenge or badge
rule changes (`app/Support/ProgressService.php`).

Each challenge and badge has a **"How progress is counted"** rule:

| Rule | Counts |
|---|---|
| Verified distance (km) | Sum of km |
| Number of verified rides | Rides |
| Number of different days ridden | Distinct ride dates |
| Rides started before 9:00 AM | Rides with a ride time before 9:00 |
| Set by admin | Nothing automatic: set it on the rider's page |

- **Challenges** count rides between their start and end dates (or from the day
  the rider joined). Reaching the goal marks it Completed, alerts the rider and
  unlocks the challenge's reward badge, if one is set. If a ride is rejected
  later, the challenge re-opens.
- **Badges** with an automatic rule count all-time rides and unlock by
  themselves at the goal. A badge is never locked again automatically.
- **Next Badge** (home page) is the locked badge with an automatic rule the
  rider is closest to (highest %, then badge order), with what's left to do
  (“64.3 km to go”) and rides waiting for review as a striped bar. Badges set by
  the admin aren't shown there, because rides don't move them. In the demo data
  Power Pedal (5 days ridden), Early Bird (3 morning rides) and Century Club
  (100 km) are automatic.
- Riders join challenges with **Accept Challenge**; **Log Progress** opens the
  upload form. Challenges are only shown while live and within their dates.

## Website features

Upload Ride (logged-in riders; saved as pending with the photo proof, validated,
duplicate check, up to 3 uploads per rider per day), Accept Challenge, Log Progress,
My Progress, View All History (with filters), Trophy Room, notifications
(unread count in the header, marked seen when opened), Settings (name, mobile
and photo), Log Out.

The site runs on India time (`APP_TIMEZONE=Asia/Kolkata`), so "today" and
"before 9 AM" match the riders' clocks.

## Rider registration and login (email + OTP)

- **Join:** `/join` (“Join the Adventure”): rider name, username, mobile number
  and Gmail/email ID. Tap **Send OTP to my email**, type the 6-digit code from
  the email, then **Create My Account**. The account is created with the email
  marked as verified. Taken usernames and already-registered emails are caught
  before the email goes out.
- **Log in:** `/login`: email → **Send OTP to my email** → code → **Let's Ride!**
- Uploading rides, progress, ride history, trophies, alerts, settings and
  joining challenges need a logged-in rider; other pages are open to everyone.
- Codes: 6 digits, valid 10 minutes, usable once, 5 tries; a new code can be
  sent once a minute (and at most 3 a minute / 20 an hour per email).
- The mobile number is contact information only (a parent's number can be shared
  by brothers and sisters). Admins can change a rider's email or username in
  Admin → Riders; a changed email counts as verified after the rider's next login.
- Demo rider: `alex@kidsavon.test` (with `OTP_TEST_CODE` set, see below).

**Local testing without email:** set `OTP_TEST_CODE=1234` in `.env`; then no
email is sent and every code is 1234. **Leave it empty on the live site.**

## Sending OTP emails (SMTP) and keeping them out of spam

Fill in the `MAIL_*` lines in `.env`. Any SMTP mailbox works, for example:

| Option | MAIL_HOST | MAIL_PORT | MAIL_USERNAME / MAIL_PASSWORD | MAIL_FROM_ADDRESS |
|---|---|---|---|---|
| Your hosting's email (cPanel etc.) – recommended | `mail.yourdomain.com` | `587` | the mailbox and its password | that same mailbox, e.g. `noreply@yourdomain.com` |
| Brevo (free: ~300 emails/day) | `smtp-relay.brevo.com` | `587` | SMTP login + SMTP key from Brevo | an address on a domain verified in Brevo |
| Gmail (testing / small use) | `smtp.gmail.com` | `587` | the Gmail address + an **App Password** (Google account → Security → 2-Step Verification → App passwords) | the same Gmail address |

Then run `php artisan config:clear`.

The email itself is written to avoid spam filters: a plain subject
(“123456 is your Kids Avon verification code”), matching HTML and plain-text
versions, no images, links or attachments, a Reply-To address (the support
email from Site settings), and a unique reference so Gmail doesn't bundle codes
together. What decides spam vs inbox most, though, is **proving the email
really comes from your domain**. In your domain's DNS add:

1. **SPF** – a TXT record on the domain, e.g. `v=spf1 include:<your mail provider> ~all`
   (your host or Brevo tells you the exact value). Only one SPF record per domain.
2. **DKIM** – the TXT/CNAME record your host or Brevo gives you (cPanel: *Email
   Deliverability* → Repair/Install; Brevo: *Senders & domains* → Authenticate).
3. **DMARC** – a TXT record at `_dmarc.yourdomain.com`: `v=DMARC1; p=none; rua=mailto:you@yourdomain.com`
   (move to `p=quarantine` once everything passes).

Always send **from the same domain you log in with** (never "from" a
@gmail.com address through another server). Check the result by sending a code
to the address shown on https://www.mail-tester.com (aim for 9/10 or more) and
to a Gmail inbox: *Show original* should say SPF, DKIM and DMARC **PASS**.

## Bot and spam protection

- **Cloudflare Turnstile** robot check on Join, Log in, Send OTP and the admin
  login. Usually invisible; sometimes a tick box. Set up: Cloudflare dashboard →
  **Turnstile** → **Add widget** → add your website's domain (and `localhost` for
  testing) → mode **Managed** → copy the **Site key** and **Secret key** into
  `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` in `.env` → `php artisan config:clear`.
  The keys in `.env.example` are Cloudflare's testing keys (they always pass).
- **Hidden trap field:** every form has a box people never see; bots that fill
  it in are quietly turned away (nothing saved, no email sent).
- **Too-fast check:** forms sent back within 2 seconds of opening are treated
  as bots (`kidsavon.bot_guard.min_seconds`).
- **Upload limit:** 3 ride uploads per rider per day (change in Admin → Site
  settings).
- Rate limits on codes, logins, uploads and the admin login.

The admin **Dashboard** shows a “Before going live” box while testing keys,
OTP testing mode or an unconfigured mailer are still in use.

## Progress while rides wait for review

New uploads appear straight away on the rider's progress page, challenge cards
and home page as **“waiting for review”** (a striped part of the progress bar).
They count once the admin verifies them. To count rides immediately, tick
**Count rides as soon as they are uploaded** in Admin → Site settings.
