<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Faq;
use App\Models\Rider;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Recreates the placeholder content of the original static HTML pages
 * (riders, rides, badges, challenges, notifications, FAQs) so the Laravel
 * version renders with realistic data until the admin panel manages it.
 */
class KidsAvonDemoSeeder extends Seeder
{
    private const AVATAR = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuh7NkhUtOMFc6JuaD-jHCZTiP32zOtiFu92g3O7RbaDVr0qCpDefyljNFPRyl9Zmb86qrfOIrN_ao-6dhdhijoE2kU9aCEHg4UcmSZ0O2frhN5CQid38YwVPnGkOHxN488v4SKJPTOdjSwbQwnxMuomSUFiuEFRt3ITGTlLHvRE_K4IDttp9clDtwRJtJ_ey-VPdkoE2KWr5XOLmQQza9Koadbffoj_rHmRSheU6o5xf2zRQvQDS5';

    private const LOCKED_BADGE_IMAGE = 'https://lh3.googleusercontent.com/aida/AP1WRLtgqOOcW1Bga3aI3iDqkULOk866sMb_zfq_h-A3GCEPg3QzVACL8d73uNUg_-3gWSXxg2AX9E36nFw9BK-BGGrER3nnY5WEJcJxp3--zpnRTMz17mEGBflKbKNbYZlAu2rgU23SOWrQQABle2u8ug22bsnUdxM6aXPZvo92m1rEoNoVgVN2jzYxVQ5zPWS-Sj_xixX20M9TVzyMycNCau7MgDvFMV6EET71q3bnqzaSSeWXqqBEpxf_Tg';

    public function run(): void
    {
        $this->seedSettings();
        $this->seedFaqs();
        $badges = $this->seedBadges();
        $challenges = $this->seedChallenges();
        $this->seedLeaderboardRiders();
        $this->seedDemoRider($badges, $challenges);
    }

    private function seedSettings(): void
    {
        foreach ([
            'community_miles_today' => 12450,
            'community_goal_km' => 150,
            'community_progress_km' => 112.5,
            'support_email' => 'avon@avoncycles.com',
        ] as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['help', 'secondary', 'How do I participate?', 'Just grab your bike, helmet, and start riding! Track your miles using Strava and upload them here.'],
            ['account_circle', 'tertiary', 'Do I need a Strava account?', 'Yes, we use Strava to verify your rides. Ask a parent to help you set one up!'],
            ['directions_bike', 'primary', 'What activities are eligible?', "Any bicycle ride counts! Just make sure you're riding safely on designated paths."],
            ['screenshot', 'secondary', 'What should my screenshot show?', 'Your screenshot must clearly show the activity date and distance (at least 1 mile!).'],
            ['military_tech', 'tertiary', 'When will I receive my reward?', 'Badges are unlocked immediately after verification. Keep riding to level up!'],
            ['error', 'neutral', 'What happens if my submission is rejected?', "We'll let you know why. Usually, it's just a missing date or distance."],
        ];

        foreach ($faqs as $i => [$icon, $color, $question, $answer]) {
            Faq::create(compact('icon', 'color', 'question', 'answer') + ['sort_order' => $i + 1]);
        }
    }

    /** @return array<string, Badge> */
    private function seedBadges(): array
    {
        $rows = [
            // Home page "New Badges to Earn"
            ['Speed Star', 'Hit 10mph', 'bolt', 'primary', 'trophy2.jpg', true, false],
            ['Power Pedal', 'Ride 5 days', 'electric_bike', 'tertiary', self::LOCKED_BADGE_IMAGE, true, false],
            ['Champion', 'Top 10 locally', 'military_tech', 'primary', self::LOCKED_BADGE_IMAGE, true, false],
            // Trophy Room
            ['Explorer', 'Rode 10km in a single week.', 'map', 'primary', null, false, true],
            ['Speedster', 'Completed a 5km ride under 30 mins.', 'speed', 'secondary', null, false, true],
            ['Park Hopper', 'Visited 3 different local parks.', 'nature_people', 'tertiary', null, false, true],
            ['Early Bird', 'Complete 3 morning rides before 9AM.', 'wb_sunny', 'tertiary', null, false, true],
            ['Social Butterfly', 'Add 5 friends to your Kids Avon list.', 'groups', 'secondary', null, false, true],
            ['Century Club', 'Ride a total of 100km.', 'directions_bike', 'primary', null, false, true],
        ];

        $badges = [];
        foreach ($rows as $i => [$name, $description, $icon, $color, $image, $home, $trophy]) {
            $badges[$name] = Badge::create([
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'color' => $color,
                'image' => $image,
                'show_on_home' => $home,
                'show_in_trophy_room' => $trophy,
                'sort_order' => $i + 1,
            ]);
        }

        return $badges;
    }

    /** @return array<string, Challenge> */
    private function seedChallenges(): array
    {
        $rows = [
            ['10km Weekly Milestone', 'Ride a total of 10 kilometers this week to earn the Explorer Badge!', 'map', 'primary', 500, 10, 'km', 'Progress'],
            ['Park Hopper', 'Visit 3 different local parks on your rides this weekend.', 'nature_people', 'tertiary', 300, 3, null, 'Parks Visited'],
            ['Early Bird Special', 'Complete 3 morning rides before 9:00 AM this month.', 'wb_sunny', 'secondary', 800, 3, null, 'Morning Rides'],
        ];

        $challenges = [];
        foreach ($rows as $i => [$title, $description, $icon, $color, $points, $target, $unit, $label]) {
            $challenges[$title] = Challenge::create([
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
                'color' => $color,
                'reward_points' => $points,
                'target_value' => $target,
                'unit' => $unit,
                'progress_label' => $label,
                'sort_order' => $i + 1,
            ]);
        }

        return $challenges;
    }

    /** Other riders, with verified kilometres matching the original Hall of Fame. */
    private function seedLeaderboardRiders(): void
    {
        $riders = [
            ['Sarah Wheeler', '9000000001', self::AVATAR, 14, [25, 20]],
            ['Jake Doe', '9000000002', null, 13, [22, 20]],
            ['Mia Luna', '9000000003', null, 12, [20, 20]],
            ['Ryan King', '9000000004', null, 10, [20, 15]],
            ['Emma P.', '9000000005', null, 9, [16, 15]],
        ];

        foreach ($riders as $index => [$name, $mobile, $avatar, $level, $rides]) {
            $rider = Rider::create(compact('name', 'mobile', 'avatar', 'level'));

            // A couple of uploads waiting in the admin review queue.
            if (in_array($index, [1, 2], true)) {
                $rider->rides()->create([
                    'title' => $index === 1 ? 'Evening Lake Loop' : 'Ride to Grandma\'s',
                    'ride_date' => Carbon::yesterday(),
                    'ride_time' => '17:30',
                    'distance_km' => $index === 1 ? 6.2 : 3.5,
                    'duration_minutes' => $index === 1 ? 32 : 20,
                    'status' => 'pending',
                ]);
            }

            foreach ($rides as $i => $km) {
                $rider->rides()->create([
                    'ride_date' => Carbon::today()->subDays(3 + $i * 4),
                    'distance_km' => $km,
                    'duration_minutes' => (int) round($km * 5),
                    'status' => 'verified',
                    'reviewed_at' => now(),
                ]);
            }
        }
    }

    /**
     * The logged-in demo rider ("Alex Rider") shown in the header,
     * progress page, trophy room, challenges and notifications.
     *
     * @param  array<string, Badge>  $badges
     * @param  array<string, Challenge>  $challenges
     */
    private function seedDemoRider(array $badges, array $challenges): void
    {
        $alex = Rider::create([
            'name' => 'Alex Rider',
            'mobile' => config('kidsavon.demo_rider_mobile'),
            'avatar' => self::AVATAR,
            'level' => 12,
        ]);

        // Rides (the four most recent are the ones from the original progress page).
        $rides = [
            ['Weekend Trail Explorer', '2024-10-12', 15.2, 80, 'verified', null],
            ['After School Spin', '2024-10-10', 5.0, 35, 'pending', null],
            ['Family Park Loop', '2024-10-05', 8.5, 50, 'verified', null],
            ['Short Grocery Run', '2024-10-02', 0.8, 10, 'rejected', 'Distance too short'],
            ['Neighbourhood Loop', '2024-09-28', 3.0, 20, 'verified', null],
            ['Morning Warm-up', '2024-09-21', 2.5, 15, 'verified', null],
        ];
        foreach ($rides as [$title, $date, $km, $minutes, $status, $reason]) {
            $alex->rides()->create([
                'title' => $title,
                'ride_date' => $date,
                'distance_km' => $km,
                'duration_minutes' => $minutes,
                'status' => $status,
                'rejection_reason' => $reason,
                'reviewed_at' => $status === 'pending' ? null : now(),
            ]);
        }

        // Badges: unlocked ones get a date, locked ones a progress percentage.
        $badgeState = [
            'Speed Star' => ['unlocked_at' => '2026-05-10', 'progress_percent' => 100],
            'Power Pedal' => ['unlocked_at' => null, 'progress_percent' => 0],
            'Champion' => ['unlocked_at' => null, 'progress_percent' => 0],
            'Explorer' => ['unlocked_at' => '2026-08-02', 'progress_percent' => 100],
            'Speedster' => ['unlocked_at' => '2026-07-15', 'progress_percent' => 100],
            'Park Hopper' => ['unlocked_at' => '2026-06-28', 'progress_percent' => 100],
            'Early Bird' => ['unlocked_at' => null, 'progress_percent' => 0],
            'Social Butterfly' => ['unlocked_at' => null, 'progress_percent' => 40],
            'Century Club' => ['unlocked_at' => null, 'progress_percent' => 75],
        ];
        foreach ($badgeState as $name => $pivot) {
            $alex->badges()->attach($badges[$name]->id, $pivot);
        }

        // Challenges Alex has already accepted.
        $alex->challenges()->attach($challenges['10km Weekly Milestone']->id, ['progress_value' => 6.5]);
        $alex->challenges()->attach($challenges['Park Hopper']->id, ['progress_value' => 1]);

        // Notifications.
        $notifications = [
            ['New Badge Unlocked!', 'You earned the "Weekend Warrior" badge for riding 3 days in a row.', 'stars', 'primary', now()->subHours(2), false],
            ['Friend Activity', 'Timmy completed the "Park Hopper" challenge!', 'group', 'secondary', now()->subHours(5), false],
            ['Ride Verified', 'Your 4km ride to Central Park has been verified by your parent.', 'pedal_bike', 'tertiary', now()->subDay(), true],
            ['New Challenge Available', 'The "Summer Sprint" challenge is now open. Join to earn double points!', 'campaign', 'neutral', now()->subDays(2), true],
        ];
        foreach ($notifications as [$title, $message, $icon, $color, $at, $read]) {
            $n = $alex->notifications()->create(compact('title', 'message', 'icon', 'color') + [
                'read_at' => $read ? $at : null,
            ]);
            $n->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }
    }
}
