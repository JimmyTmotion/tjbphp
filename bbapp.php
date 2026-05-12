<?php
require_once __DIR__ . '/includes/bbapp-stats.php';

bbapp_dashboard_bootstrap();
bbapp_require_dashboard_password();

$range = bbapp_range();
$selectedProfileId = bbapp_selected_profile_id();
$dashboard = null;
$dashboardError = null;

try {
    $dashboard = bbapp_build_dashboard(bbapp_pdo(), $range, $selectedProfileId);
} catch (Throwable $error) {
    $dashboardError = $error->getMessage();
}

$pageTitle = 'BBSensory App Stats | Tom J Butler';
$currentPage = 'bbapp';
$extraStyles = ['assets/css/bbapp.css'];
$extraScripts = $dashboard === null ? [] : [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js',
    'assets/js/bbapp-dashboard.js',
];

function bbapp_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function bbapp_number($value): string
{
    return number_format((float) $value);
}

function bbapp_kpi(string $label, $value, string $detail): void
{
    ?>
    <article class="bbapp-kpi-card">
        <span><?php echo bbapp_e($label); ?></span>
        <strong><?php echo bbapp_e($value); ?></strong>
        <small><?php echo bbapp_e($detail); ?></small>
    </article>
    <?php
}

function bbapp_table_empty(array $rows, int $columns): void
{
    if ($rows !== []) {
        return;
    }
    ?>
    <tr>
        <td colspan="<?php echo $columns; ?>">No data for this date range.</td>
    </tr>
    <?php
}

include __DIR__ . '/includes/page-start.php';
?>

<main class="bbapp-dashboard">
    <section class="bbapp-hero">
        <div class="container">
            <div class="bbapp-hero-panel">
                <div>
                    <p class="bbapp-eyebrow">Private app analytics</p>
                    <h1>BBSensory stats dashboard</h1>
                    <p>Monitor app users, premium status, playback activity, content health, and feature engagement from the Supabase database.</p>
                </div>
                <form class="bbapp-range" method="get" aria-label="Dashboard date range">
                    <?php foreach (['7' => '7D', '30' => '30D', '90' => '90D', '180' => '180D', '365' => '1Y', 'all' => 'All'] as $key => $label): ?>
                        <button type="submit" name="range" value="<?php echo bbapp_e($key); ?>" class="<?php echo $range['key'] === $key ? 'is-active' : ''; ?>">
                            <?php echo bbapp_e($label); ?>
                        </button>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>
    </section>

    <?php if ($dashboardError !== null): ?>
        <section class="bbapp-section">
            <div class="container">
                <div class="bbapp-setup-card">
                    <p class="bbapp-eyebrow">Setup required</p>
                    <h2>Database connection is not ready</h2>
                    <p><?php echo bbapp_e($dashboardError); ?></p>
                    <p>Add a root <code>.env</code> file using <code>.env.example</code> as the template. The dashboard expects <code>BBAPP_DATABASE_URL</code> and should also use <code>BBAPP_DASHBOARD_PASSWORD</code> before this page is deployed publicly.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <script type="application/json" id="bbapp-dashboard-data"><?php echo json_encode($dashboard['charts'], JSON_THROW_ON_ERROR); ?></script>

        <?php if (tjb_env('BBAPP_DASHBOARD_PASSWORD') === null): ?>
            <section class="bbapp-section bbapp-tight-section">
                <div class="container">
                    <div class="bbapp-warning">
                        <strong>Password not configured.</strong>
                        <span>Set <code>BBAPP_DASHBOARD_PASSWORD</code> in <code>.env</code> before making this page public.</span>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="bbapp-section bbapp-tight-section">
            <div class="container">
                <div class="bbapp-section-heading">
                    <div>
                        <p class="bbapp-eyebrow"><?php echo bbapp_e($dashboard['range']['label']); ?></p>
                        <h2>Overview</h2>
                    </div>
                    <span>Generated <?php echo bbapp_e($dashboard['generatedAt']); ?></span>
                </div>
                <div class="bbapp-kpi-grid">
                    <?php bbapp_kpi('Total users', bbapp_number($dashboard['kpis']['totalUsers']), 'Profiles in Supabase'); ?>
                    <?php bbapp_kpi('New today', bbapp_number($dashboard['kpis']['newToday']), 'Profiles created today'); ?>
                    <?php bbapp_kpi('New 7 days', bbapp_number($dashboard['kpis']['new7Days']), 'Recent acquisition'); ?>
                    <?php bbapp_kpi('New 30 days', bbapp_number($dashboard['kpis']['new30Days']), 'Monthly acquisition'); ?>
                    <?php bbapp_kpi('Active 24h', bbapp_number($dashboard['kpis']['active24h']), 'Users with playback'); ?>
                    <?php bbapp_kpi('Active 7 days', bbapp_number($dashboard['kpis']['active7Days']), 'Weekly active users'); ?>
                    <?php bbapp_kpi('Active 30 days', bbapp_number($dashboard['kpis']['active30Days']), 'Monthly active users'); ?>
                    <?php bbapp_kpi('Effective premium', bbapp_number($dashboard['kpis']['effectivePremiumUsers']), 'Premium or override'); ?>
                    <?php bbapp_kpi('Paid premium', bbapp_number($dashboard['kpis']['paidPremiumUsers']), 'Excludes overrides'); ?>
                    <?php bbapp_kpi('Conversion rate', $dashboard['kpis']['premiumConversionRate'] . '%', 'Paid premium / users'); ?>
                    <?php bbapp_kpi('DAU / MAU', $dashboard['kpis']['stickinessRate'] . '%', '24h active / 30d active'); ?>
                    <?php bbapp_kpi('Plays in range', bbapp_number($dashboard['kpis']['playEventsInRange']), 'All play events'); ?>
                    <?php bbapp_kpi('Active in range', bbapp_number($dashboard['kpis']['activeUsersInRange']), 'Selected range users'); ?>
                    <?php bbapp_kpi('Avg plays / active user', $dashboard['kpis']['avgEventsPerActiveUser'], 'In selected range'); ?>
                    <?php bbapp_kpi('Returning users', bbapp_number($dashboard['kpis']['returningUsers']), 'Active on 2+ days'); ?>
                    <?php bbapp_kpi('Never played', bbapp_number($dashboard['kpis']['usersNeverPlayed']), 'Profiles with no playback'); ?>
                </div>
            </div>
        </section>

        <section class="bbapp-section">
            <div class="container">
                <div class="bbapp-grid bbapp-grid-two">
                    <article class="bbapp-panel bbapp-chart-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Profiles over time</h3>
                            <p>New profiles and cumulative profile count.</p>
                        </div>
                        <canvas data-bbapp-chart="profiles"></canvas>
                    </article>
                    <article class="bbapp-panel bbapp-chart-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Daily active users</h3>
                            <p>Distinct users with playback events per day.</p>
                        </div>
                        <canvas data-bbapp-chart="dailyActiveUsers"></canvas>
                    </article>
                    <article class="bbapp-panel bbapp-chart-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Theme preference</h3>
                            <p>Dark mode vs light mode from user settings.</p>
                        </div>
                        <canvas data-bbapp-chart="themeMode"></canvas>
                    </article>
                    <article class="bbapp-panel bbapp-chart-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Access status</h3>
                            <p>Free, premium, tester override, and combined users.</p>
                        </div>
                        <canvas data-bbapp-chart="premiumStatus"></canvas>
                    </article>
                </div>
            </div>
        </section>

        <section class="bbapp-section">
            <div class="container">
                <div class="bbapp-section-heading">
                    <div>
                        <p class="bbapp-eyebrow">Playback</p>
                        <h2>Feature usage</h2>
                    </div>
                </div>
                <div class="bbapp-grid bbapp-grid-two">
                    <article class="bbapp-panel bbapp-chart-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Playback by feature</h3>
                            <p>Daily play events split by content type.</p>
                        </div>
                        <canvas data-bbapp-chart="playbackByType"></canvas>
                    </article>
                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Content type totals</h3>
                            <p>Play events in the selected range.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Plays</th>
                                        <th>Unique users</th>
                                        <th>Last played</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['playbackTypes'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e(str_replace('_', ' ', $row['content_type'])); ?></td>
                                            <td><?php echo bbapp_number($row['plays']); ?></td>
                                            <td><?php echo bbapp_number($row['unique_users']); ?></td>
                                            <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['playbackTypes'], 4); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="bbapp-section">
            <div class="container">
                <div class="bbapp-section-heading">
                    <div>
                        <p class="bbapp-eyebrow">Content detail</p>
                        <h2>Top played content</h2>
                    </div>
                    <input class="bbapp-table-search" type="search" placeholder="Filter tables" data-bbapp-table-search>
                </div>
                <div class="bbapp-grid bbapp-grid-two">
                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Videos</h3>
                            <p>Video names and number of plays.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>Video</th><th>Access</th><th>Plays</th><th>Users</th><th>Last played</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['videos'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e($row['title']); ?></td>
                                            <td><?php echo $row['is_premium'] ? 'Premium' : 'Free'; ?></td>
                                            <td><?php echo bbapp_number($row['plays']); ?></td>
                                            <td><?php echo bbapp_number($row['unique_users']); ?></td>
                                            <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['videos'], 5); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Sound tracks</h3>
                            <p>Sound names and number of plays.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>Sound</th><th>Access</th><th>Plays</th><th>Users</th><th>Last played</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['sounds'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e($row['title']); ?></td>
                                            <td><?php echo $row['is_premium'] ? 'Premium' : 'Free'; ?></td>
                                            <td><?php echo bbapp_number($row['plays']); ?></td>
                                            <td><?php echo bbapp_number($row['unique_users']); ?></td>
                                            <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['sounds'], 5); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Flash card packs</h3>
                            <p>Pack and all-available session usage.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>Pack</th><th>Mode</th><th>Access</th><th>Plays</th><th>Users</th><th>Last played</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['flashcards'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e($row['title']); ?></td>
                                            <td><?php echo bbapp_e($row['mode']); ?></td>
                                            <td><?php echo $row['is_premium'] ? 'Premium' : 'Free'; ?></td>
                                            <td><?php echo bbapp_number($row['plays']); ?></td>
                                            <td><?php echo bbapp_number($row['unique_users']); ?></td>
                                            <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['flashcards'], 6); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Quick Calm</h3>
                            <p>Mode popularity and usage depth.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>Mode</th><th>Plays</th><th>Users</th><th>Last played</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['quickCalm'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e(str_replace('-', ' ', $row['title'])); ?></td>
                                            <td><?php echo bbapp_number($row['plays']); ?></td>
                                            <td><?php echo bbapp_number($row['unique_users']); ?></td>
                                            <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['quickCalm'], 4); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="bbapp-section">
            <div class="container">
                <div class="bbapp-grid bbapp-grid-two">
                    <article class="bbapp-panel bbapp-wide-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Recent activity</h3>
                            <p>Latest playback events across videos, sounds, flashcards, and Quick Calm.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>User</th><th>Content</th><th>Type</th><th>Event</th><th>When</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['recentActivity'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e($row['user_suffix']); ?></td>
                                            <td><?php echo bbapp_e(str_replace('-', ' ', $row['title'])); ?></td>
                                            <td><?php echo bbapp_e(str_replace('_', ' ', $row['content_type'])); ?></td>
                                            <td><?php echo bbapp_e($row['event_type']); ?></td>
                                            <td><?php echo bbapp_e(bbapp_format_datetime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['recentActivity'], 5); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Content health</h3>
                            <p>Published, unpublished, free, and premium inventory.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>Content</th><th>Total</th><th>Published</th><th>Unpublished</th><th>Premium</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['contentHealth'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e($row['content']); ?></td>
                                            <td><?php echo bbapp_number($row['total']); ?></td>
                                            <td><?php echo bbapp_number($row['published']); ?></td>
                                            <td><?php echo bbapp_number($row['unpublished']); ?></td>
                                            <td><?php echo $row['premium'] === null ? 'N/A' : bbapp_number($row['premium']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="bbapp-panel">
                        <div class="bbapp-panel-heading">
                            <h3>Content with zero plays</h3>
                            <p>Items that may need promotion, QA, or removal.</p>
                        </div>
                        <div class="bbapp-table-wrap">
                            <table class="bbapp-table" data-bbapp-table>
                                <thead><tr><th>Type</th><th>Name</th><th>Access</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dashboard['tables']['zeroPlayContent'] as $row): ?>
                                        <tr>
                                            <td><?php echo bbapp_e($row['type']); ?></td>
                                            <td><?php echo bbapp_e($row['title']); ?></td>
                                            <td><?php echo $row['is_premium'] ? 'Premium' : 'Free'; ?></td>
                                            <td><?php echo $row['is_published'] ? 'Published' : 'Unpublished'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php bbapp_table_empty($dashboard['tables']['zeroPlayContent'], 4); ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="bbapp-section">
            <div class="container">
                <div class="bbapp-section-heading">
                    <div>
                        <p class="bbapp-eyebrow">Profiles</p>
                        <h2>Usage by profile</h2>
                    </div>
                    <span>Profiles are identified by the final 6 UUID characters.</span>
                </div>

                <article class="bbapp-panel">
                    <div class="bbapp-panel-heading">
                        <h3>Most active profiles</h3>
                        <p>Ranked by total playback events across all time.</p>
                    </div>
                    <div class="bbapp-table-wrap">
                        <table class="bbapp-table" data-bbapp-table data-bbapp-sortable>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Profile</th>
                                    <th>Created</th>
                                    <th>24h</th>
                                    <th>7 days</th>
                                    <th>30 days</th>
                                    <th>All time</th>
                                    <th>Active days</th>
                                    <th>Last event</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboard['tables']['profileUsage'] as $index => $row): ?>
                                    <?php
                                    $isSelectedProfile = isset($dashboard['selectedProfile']['profile_id'])
                                        && $dashboard['selectedProfile']['profile_id'] === $row['profile_id'];
                                    $profileUrl = 'bbapp.php?range=' . rawurlencode($dashboard['range']['key']) . '&profile=' . rawurlencode($row['profile_id']) . '#profile-detail';
                                    ?>
                                    <tr class="<?php echo $isSelectedProfile ? 'is-selected' : ''; ?>">
                                        <td><?php echo $index + 1; ?></td>
                                        <td><a href="<?php echo bbapp_e($profileUrl); ?>">...<?php echo bbapp_e($row['user_suffix']); ?></a></td>
                                        <td><?php echo bbapp_e(bbapp_format_datetime($row['created_at'])); ?></td>
                                        <td><?php echo bbapp_number($row['events_24h']); ?></td>
                                        <td><?php echo bbapp_number($row['events_7d']); ?></td>
                                        <td><?php echo bbapp_number($row['events_30d']); ?></td>
                                        <td><?php echo bbapp_number($row['total_events']); ?></td>
                                        <td><?php echo bbapp_number($row['active_days']); ?></td>
                                        <td><?php echo bbapp_e(bbapp_format_datetime($row['last_event'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php bbapp_table_empty($dashboard['tables']['profileUsage'], 9); ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <div id="profile-detail" class="bbapp-profile-detail">
                    <?php if ($dashboard['selectedProfile'] === null): ?>
                        <article class="bbapp-panel">
                            <div class="bbapp-panel-heading">
                                <h3>Profile detail</h3>
                                <p>Select a profile above to see individual usage.</p>
                            </div>
                        </article>
                    <?php else: ?>
                        <div class="bbapp-section-heading bbapp-profile-heading">
                            <div>
                                <p class="bbapp-eyebrow">Selected profile</p>
                                <h2>...<?php echo bbapp_e($dashboard['selectedProfile']['user_suffix']); ?></h2>
                            </div>
                            <span>
                                Created <?php echo bbapp_e(bbapp_format_datetime($dashboard['selectedProfile']['created_at'])); ?>
                            </span>
                        </div>

                        <div class="bbapp-kpi-grid bbapp-profile-kpis">
                            <?php bbapp_kpi('Total events', bbapp_number($dashboard['selectedProfile']['total_events']), 'All playback events'); ?>
                            <?php bbapp_kpi('Last 24h', bbapp_number($dashboard['selectedProfile']['events_24h']), 'Playback events'); ?>
                            <?php bbapp_kpi('Last 7 days', bbapp_number($dashboard['selectedProfile']['events_7d']), 'Playback events'); ?>
                            <?php bbapp_kpi('Last 30 days', bbapp_number($dashboard['selectedProfile']['events_30d']), 'Playback events'); ?>
                            <?php bbapp_kpi('Active days', bbapp_number($dashboard['selectedProfile']['active_days']), 'Distinct event days'); ?>
                            <?php bbapp_kpi('Access', $dashboard['selectedProfile']['is_premium'] ? 'Premium' : ($dashboard['selectedProfile']['has_premium_override'] ? 'Override' : 'Free'), 'Current profile status'); ?>
                            <?php bbapp_kpi('Theme', $dashboard['selectedProfile']['nightmode'] === null ? 'Unknown' : ($dashboard['selectedProfile']['nightmode'] ? 'Dark' : 'Light'), 'Current setting'); ?>
                            <?php bbapp_kpi('Last event', bbapp_format_datetime($dashboard['selectedProfile']['last_event']), 'Most recent activity'); ?>
                        </div>

                        <div class="bbapp-grid bbapp-grid-two">
                            <article class="bbapp-panel bbapp-chart-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Selected profile plays over time</h3>
                                    <p>Daily play events for this profile.</p>
                                </div>
                                <canvas data-bbapp-chart="selectedProfilePlays"></canvas>
                            </article>
                            <article class="bbapp-panel bbapp-chart-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Selected profile usage mix</h3>
                                    <p>Playback events by content type.</p>
                                </div>
                                <canvas data-bbapp-chart="selectedProfileTypes"></canvas>
                            </article>

                            <article class="bbapp-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Videos watched</h3>
                                    <p>Video plays for this profile.</p>
                                </div>
                                <div class="bbapp-table-wrap">
                                    <table class="bbapp-table" data-bbapp-table>
                                        <thead><tr><th>Video</th><th>Plays</th><th>First played</th><th>Last played</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($dashboard['tables']['selectedProfile']['videos'] as $row): ?>
                                                <tr>
                                                    <td><?php echo bbapp_e($row['title']); ?></td>
                                                    <td><?php echo bbapp_number($row['plays']); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['first_played'])); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php bbapp_table_empty($dashboard['tables']['selectedProfile']['videos'], 4); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <article class="bbapp-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Sounds played</h3>
                                    <p>Sound track plays for this profile.</p>
                                </div>
                                <div class="bbapp-table-wrap">
                                    <table class="bbapp-table" data-bbapp-table>
                                        <thead><tr><th>Sound</th><th>Plays</th><th>First played</th><th>Last played</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($dashboard['tables']['selectedProfile']['sounds'] as $row): ?>
                                                <tr>
                                                    <td><?php echo bbapp_e($row['title']); ?></td>
                                                    <td><?php echo bbapp_number($row['plays']); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['first_played'])); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php bbapp_table_empty($dashboard['tables']['selectedProfile']['sounds'], 4); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <article class="bbapp-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Flash cards used</h3>
                                    <p>Flash card sessions for this profile.</p>
                                </div>
                                <div class="bbapp-table-wrap">
                                    <table class="bbapp-table" data-bbapp-table>
                                        <thead><tr><th>Pack</th><th>Mode</th><th>Plays</th><th>First played</th><th>Last played</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($dashboard['tables']['selectedProfile']['flashcards'] as $row): ?>
                                                <tr>
                                                    <td><?php echo bbapp_e($row['title']); ?></td>
                                                    <td><?php echo bbapp_e($row['mode']); ?></td>
                                                    <td><?php echo bbapp_number($row['plays']); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['first_played'])); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php bbapp_table_empty($dashboard['tables']['selectedProfile']['flashcards'], 5); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <article class="bbapp-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Quick Calm used</h3>
                                    <p>Quick Calm mode usage for this profile.</p>
                                </div>
                                <div class="bbapp-table-wrap">
                                    <table class="bbapp-table" data-bbapp-table>
                                        <thead><tr><th>Mode</th><th>Plays</th><th>First played</th><th>Last played</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($dashboard['tables']['selectedProfile']['quickCalm'] as $row): ?>
                                                <tr>
                                                    <td><?php echo bbapp_e(str_replace('-', ' ', $row['title'])); ?></td>
                                                    <td><?php echo bbapp_number($row['plays']); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['first_played'])); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['last_played'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php bbapp_table_empty($dashboard['tables']['selectedProfile']['quickCalm'], 4); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <article class="bbapp-panel bbapp-wide-panel">
                                <div class="bbapp-panel-heading">
                                    <h3>Selected profile recent events</h3>
                                    <p>Latest individual playback events for this profile.</p>
                                </div>
                                <div class="bbapp-table-wrap">
                                    <table class="bbapp-table" data-bbapp-table>
                                        <thead><tr><th>Content</th><th>Type</th><th>Event</th><th>When</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($dashboard['tables']['selectedProfile']['recentEvents'] as $row): ?>
                                                <tr>
                                                    <td><?php echo bbapp_e(str_replace('-', ' ', $row['title'])); ?></td>
                                                    <td><?php echo bbapp_e(str_replace('_', ' ', $row['content_type'])); ?></td>
                                                    <td><?php echo bbapp_e($row['event_type']); ?></td>
                                                    <td><?php echo bbapp_e(bbapp_format_datetime($row['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php bbapp_table_empty($dashboard['tables']['selectedProfile']['recentEvents'], 4); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/page-end.php'; ?>
