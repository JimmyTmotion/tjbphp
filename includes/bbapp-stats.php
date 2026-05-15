<?php

require_once __DIR__ . '/env.php';

function bbapp_dashboard_bootstrap(): void
{
    tjb_load_env(dirname(__DIR__) . '/.env');
}

function bbapp_require_dashboard_password(): void
{
    $password = tjb_env('BBAPP_DASHBOARD_PASSWORD');

    if ($password === null) {
        return;
    }

    $username = $_SERVER['PHP_AUTH_USER'] ?? null;
    $provided = $_SERVER['PHP_AUTH_PW'] ?? null;

    if ($username !== 'admin' || $provided !== $password) {
        header('WWW-Authenticate: Basic realm="BBSensory Stats"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Authentication required.';
        exit;
    }
}

function bbapp_database_config(): array
{
    $host = tjb_env('BBAPP_DB_HOST');
    $dbname = tjb_env('BBAPP_DB_NAME');
    $user = tjb_env('BBAPP_DB_USER');
    $password = tjb_env('BBAPP_DB_PASSWORD');

    if ($host !== null || $dbname !== null || $user !== null || $password !== null) {
        if ($host === null || $dbname === null || $user === null || $password === null) {
            throw new RuntimeException('Database connection is incomplete. Set BBAPP_DB_HOST, BBAPP_DB_NAME, BBAPP_DB_USER, and BBAPP_DB_PASSWORD.');
        }

        return [
            'host' => $host,
            'port' => tjb_env('BBAPP_DB_PORT', '5432'),
            'dbname' => $dbname,
            'user' => $user,
            'password' => $password,
            'sslmode' => tjb_env('BBAPP_DB_SSLMODE', 'require'),
        ];
    }

    $url = tjb_env('BBAPP_DATABASE_URL') ?: tjb_env('DATABASE_URL') ?: tjb_env('SUPABASE_DB_URL');

    if ($url !== null) {
        $parts = parse_url($url);

        if ($parts === false || empty($parts['host']) || empty($parts['user']) || empty($parts['path'])) {
            throw new RuntimeException('BBAPP_DATABASE_URL is not a valid Postgres connection string.');
        }

        parse_str($parts['query'] ?? '', $query);

        return [
            'host' => $parts['host'],
            'port' => isset($parts['port']) ? (string) $parts['port'] : '5432',
            'dbname' => ltrim($parts['path'], '/'),
            'user' => rawurldecode($parts['user']),
            'password' => isset($parts['pass']) ? rawurldecode($parts['pass']) : '',
            'sslmode' => $query['sslmode'] ?? 'require',
        ];
    }

    throw new RuntimeException('Database connection is not configured. Add BBAPP_DB_* fields or BBAPP_DATABASE_URL to the website .env file.');
}

function bbapp_pdo(): PDO
{
    $config = bbapp_database_config();

    if (!extension_loaded('pdo_pgsql')) {
        throw new RuntimeException('The PHP pdo_pgsql extension is not enabled.');
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['sslmode']
    );

    return new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function bbapp_range(): array
{
    $allowed = ['7', '30', '90', '180', '365', 'all'];
    $range = isset($_GET['range']) ? (string) $_GET['range'] : '30';

    if (!in_array($range, $allowed, true)) {
        $range = '30';
    }

    if ($range === 'all') {
        return [
            'key' => 'all',
            'label' => 'All time',
            'start' => null,
        ];
    }

    $days = (int) $range;

    return [
        'key' => $range,
        'label' => 'Last ' . $days . ' days',
        'start' => (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days')->format('Y-m-d'),
    ];
}

function bbapp_query_rows(PDO $pdo, string $sql, array $params = []): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function bbapp_query_value(PDO $pdo, string $sql, array $params = []): int
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return (int) $statement->fetchColumn();
}

function bbapp_date_params(array $range): array
{
    return $range['start'] === null ? [] : [':start_date' => $range['start']];
}

function bbapp_date_filter(string $column, array $range, string $prefix = 'WHERE'): string
{
    if ($range['start'] === null) {
        return '';
    }

    return ' ' . $prefix . ' ' . $column . ' >= :start_date';
}

function bbapp_day_series(DateTimeImmutable $start, DateTimeImmutable $end): array
{
    $days = [];

    for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
        $days[$day->format('Y-m-d')] = 0;
    }

    return $days;
}

function bbapp_series_from_rows(array $days, array $rows, string $valueKey = 'value'): array
{
    foreach ($rows as $row) {
        $day = (string) $row['day'];

        if (array_key_exists($day, $days)) {
            $days[$day] = (int) $row[$valueKey];
        }
    }

    return [
        'labels' => array_keys($days),
        'values' => array_values($days),
    ];
}

function bbapp_format_datetime(?string $value): string
{
    if ($value === null || $value === '') {
        return 'Never';
    }

    return (new DateTimeImmutable($value))->format('d M Y, H:i');
}

function bbapp_selected_profile_id(): ?string
{
    $profileId = isset($_GET['profile']) ? trim((string) $_GET['profile']) : '';

    if ($profileId === '') {
        return null;
    }

    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $profileId)
        ? $profileId
        : null;
}

function bbapp_build_dashboard(PDO $pdo, array $range, ?string $selectedProfileId = null): array
{
    $params = bbapp_date_params($range);
    $dateFilter = bbapp_date_filter('pe.created_at', $range, 'AND');
    $andDateFilter = bbapp_date_filter('created_at', $range, 'AND');
    $joinedEventDateFilter = bbapp_date_filter('pe.created_at', $range, 'AND');
    $nonAdminProfileFilter = 'COALESCE(is_admin, FALSE) IS NOT TRUE AND COALESCE(is_tester, FALSE) IS NOT TRUE';
    $nonAdminProfileAliasFilter = 'COALESCE(p.is_admin, FALSE) IS NOT TRUE AND COALESCE(p.is_tester, FALSE) IS NOT TRUE';
    $playbackProfileJoin = "
        INNER JOIN profiles p
            ON p.id = pe.user_id
           AND COALESCE(p.is_admin, FALSE) IS NOT TRUE
           AND COALESCE(p.is_tester, FALSE) IS NOT TRUE
    ";

    $minDate = bbapp_query_rows($pdo, "
        SELECT LEAST(
            COALESCE((SELECT MIN(created_at)::date FROM profiles WHERE " . $nonAdminProfileFilter . "), CURRENT_DATE),
            COALESCE((
                SELECT MIN(pe.created_at)::date
                FROM playback_events pe
                " . $playbackProfileJoin . "
            ), CURRENT_DATE)
        )::text AS min_date
    ")[0]['min_date'] ?? date('Y-m-d');

    $start = $range['start'] === null ? new DateTimeImmutable($minDate) : new DateTimeImmutable($range['start']);
    $end = new DateTimeImmutable('today');
    $dayTemplate = bbapp_day_series($start, $end);

    $profileRows = bbapp_query_rows($pdo, "
        SELECT created_at::date::text AS day, COUNT(*) AS value
        FROM profiles
        WHERE " . $nonAdminProfileFilter . "
          AND created_at::date BETWEEN :start_day AND :end_day
        GROUP BY 1
        ORDER BY 1
    ", [
        ':start_day' => $start->format('Y-m-d'),
        ':end_day' => $end->format('Y-m-d'),
    ]);

    $profileDaily = bbapp_series_from_rows($dayTemplate, $profileRows);
    $usersBeforeStart = bbapp_query_value($pdo, "
        SELECT COUNT(*)
        FROM profiles
        WHERE " . $nonAdminProfileFilter . "
          AND created_at::date < :start_day
    ", [':start_day' => $start->format('Y-m-d')]);
    $runningTotal = $usersBeforeStart;
    $profileCumulative = [];

    foreach ($profileDaily['values'] as $value) {
        $runningTotal += $value;
        $profileCumulative[] = $runningTotal;
    }

    $dauRows = bbapp_query_rows($pdo, "
        SELECT pe.created_at::date::text AS day, COUNT(DISTINCT pe.user_id) AS value
        FROM playback_events pe
        " . $playbackProfileJoin . "
        WHERE pe.created_at::date BETWEEN :start_day AND :end_day
        GROUP BY 1
        ORDER BY 1
    ", [
        ':start_day' => $start->format('Y-m-d'),
        ':end_day' => $end->format('Y-m-d'),
    ]);

    $contentTypes = ['video', 'sound', 'flashcards', 'quick_calm'];
    $playbackByType = [];

    foreach ($contentTypes as $type) {
        $rows = bbapp_query_rows($pdo, "
            SELECT pe.created_at::date::text AS day, COUNT(*) AS value
            FROM playback_events pe
            " . $playbackProfileJoin . "
            WHERE event_type = 'play'
              AND content_type = :content_type
              AND pe.created_at::date BETWEEN :start_day AND :end_day
            GROUP BY 1
            ORDER BY 1
        ", [
            ':content_type' => $type,
            ':start_day' => $start->format('Y-m-d'),
            ':end_day' => $end->format('Y-m-d'),
        ]);

        $playbackByType[$type] = bbapp_series_from_rows($dayTemplate, $rows);
    }

    $activeUsersInRange = bbapp_query_value($pdo, "
        SELECT COUNT(DISTINCT user_id)
        FROM playback_events pe
        " . $playbackProfileJoin . "
        WHERE 1 = 1" . $joinedEventDateFilter . "
    ", $params);
    $playEventsInRange = bbapp_query_value($pdo, "
        SELECT COUNT(*)
        FROM playback_events pe
        " . $playbackProfileJoin . "
        WHERE event_type = 'play'" . $joinedEventDateFilter . "
    ", $params);
    $totalUsers = bbapp_query_value($pdo, "
        SELECT COUNT(*)
        FROM profiles
        WHERE " . $nonAdminProfileFilter . "
    ");
    $effectivePremiumUsers = bbapp_query_value($pdo, "
        SELECT COUNT(*)
        FROM profiles
        WHERE " . $nonAdminProfileFilter . "
          AND (is_premium IS TRUE OR has_premium_override IS TRUE)
    ");
    $paidPremiumUsers = bbapp_query_value($pdo, "
        SELECT COUNT(*)
        FROM profiles
        WHERE " . $nonAdminProfileFilter . "
          AND is_premium IS TRUE
    ");
    $active24h = bbapp_query_value($pdo, "
        SELECT COUNT(DISTINCT pe.user_id)
        FROM playback_events pe
        " . $playbackProfileJoin . "
        WHERE pe.created_at >= NOW() - INTERVAL '24 hours'
    ");
    $active30Days = bbapp_query_value($pdo, "
        SELECT COUNT(DISTINCT pe.user_id)
        FROM playback_events pe
        " . $playbackProfileJoin . "
        WHERE pe.created_at >= CURRENT_DATE - INTERVAL '29 days'
    ");
    $profileUsageRows = bbapp_query_rows($pdo, "
        SELECT
            p.id::text AS profile_id,
            RIGHT(p.id::text, 6) AS user_suffix,
            p.created_at,
            p.is_premium,
            p.has_premium_override,
            COUNT(pe.id) AS total_events,
            COUNT(pe.id) FILTER (WHERE pe.created_at >= NOW() - INTERVAL '24 hours') AS events_24h,
            COUNT(pe.id) FILTER (WHERE pe.created_at >= CURRENT_DATE - INTERVAL '6 days') AS events_7d,
            COUNT(pe.id) FILTER (WHERE pe.created_at >= CURRENT_DATE - INTERVAL '29 days') AS events_30d,
            COUNT(DISTINCT pe.created_at::date) AS active_days,
            MAX(pe.created_at) AS last_event
        FROM profiles p
        LEFT JOIN playback_events pe ON pe.user_id = p.id
        WHERE " . $nonAdminProfileAliasFilter . "
        GROUP BY p.id, p.created_at, p.is_premium, p.has_premium_override
        ORDER BY total_events DESC, last_event DESC NULLS LAST, p.created_at DESC
        LIMIT 100
    ");

    if ($selectedProfileId === null && isset($profileUsageRows[0]['profile_id'])) {
        $selectedProfileId = (string) $profileUsageRows[0]['profile_id'];
    }

    $selectedProfile = null;
    $selectedProfileCharts = [
        'playsOverTime' => ['labels' => array_keys($dayTemplate), 'values' => array_values($dayTemplate)],
        'eventsByType' => [],
    ];
    $selectedProfileTables = [
        'videos' => [],
        'sounds' => [],
        'flashcards' => [],
        'quickCalm' => [],
        'recentEvents' => [],
    ];

    if ($selectedProfileId !== null) {
        $selectedRows = bbapp_query_rows($pdo, "
            SELECT
                p.id::text AS profile_id,
                RIGHT(p.id::text, 6) AS user_suffix,
                p.created_at,
                p.is_premium,
                p.has_premium_override,
                us.nightmode,
                COUNT(pe.id) AS total_events,
                COUNT(pe.id) FILTER (WHERE pe.created_at >= NOW() - INTERVAL '24 hours') AS events_24h,
                COUNT(pe.id) FILTER (WHERE pe.created_at >= CURRENT_DATE - INTERVAL '6 days') AS events_7d,
                COUNT(pe.id) FILTER (WHERE pe.created_at >= CURRENT_DATE - INTERVAL '29 days') AS events_30d,
                COUNT(DISTINCT pe.created_at::date) AS active_days,
                MIN(pe.created_at) AS first_event,
                MAX(pe.created_at) AS last_event
            FROM profiles p
            LEFT JOIN user_settings us ON us.user_id = p.id
            LEFT JOIN playback_events pe ON pe.user_id = p.id
            WHERE p.id = :profile_id
              AND " . $nonAdminProfileAliasFilter . "
            GROUP BY p.id, p.created_at, p.is_premium, p.has_premium_override, us.nightmode
        ", [':profile_id' => $selectedProfileId]);

        if (isset($selectedRows[0])) {
            $selectedProfile = $selectedRows[0];

            $selectedProfileCharts['playsOverTime'] = bbapp_series_from_rows($dayTemplate, bbapp_query_rows($pdo, "
                SELECT created_at::date::text AS day, COUNT(*) AS value
                FROM playback_events
                WHERE user_id = :profile_id
                  AND event_type = 'play'
                  AND created_at::date BETWEEN :start_day AND :end_day
                GROUP BY 1
                ORDER BY 1
            ", [
                ':profile_id' => $selectedProfileId,
                ':start_day' => $start->format('Y-m-d'),
                ':end_day' => $end->format('Y-m-d'),
            ]));

            $selectedProfileCharts['eventsByType'] = bbapp_query_rows($pdo, "
                SELECT content_type AS label, COUNT(*) AS value
                FROM playback_events
                WHERE user_id = :profile_id" . $andDateFilter . "
                GROUP BY content_type
                ORDER BY value DESC
            ", array_merge([':profile_id' => $selectedProfileId], $params));

            $selectedProfileTables['videos'] = bbapp_query_rows($pdo, "
                SELECT COALESCE(v.title, 'Unknown video') AS title,
                       COUNT(pe.id) AS plays,
                       MIN(pe.created_at) AS first_played,
                       MAX(pe.created_at) AS last_played
                FROM playback_events pe
                LEFT JOIN videos v ON v.id = pe.video_id
                WHERE pe.user_id = :profile_id
                  AND pe.content_type = 'video'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY v.title
                ORDER BY plays DESC, last_played DESC
            ", array_merge([':profile_id' => $selectedProfileId], $params));

            $selectedProfileTables['sounds'] = bbapp_query_rows($pdo, "
                SELECT COALESCE(st.title, 'Unknown sound') AS title,
                       COUNT(pe.id) AS plays,
                       MIN(pe.created_at) AS first_played,
                       MAX(pe.created_at) AS last_played
                FROM playback_events pe
                LEFT JOIN sound_tracks st ON st.id = pe.sound_track_id
                WHERE pe.user_id = :profile_id
                  AND pe.content_type = 'sound'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY st.title
                ORDER BY plays DESC, last_played DESC
            ", array_merge([':profile_id' => $selectedProfileId], $params));

            $selectedProfileTables['flashcards'] = bbapp_query_rows($pdo, "
                SELECT
                    COALESCE(fp.title, CASE WHEN pe.flashcard_mode = 'all-available' THEN 'All available cards' ELSE 'Unknown pack' END) AS title,
                    COALESCE(pe.flashcard_mode, 'pack') AS mode,
                    COUNT(pe.id) AS plays,
                    MIN(pe.created_at) AS first_played,
                    MAX(pe.created_at) AS last_played
                FROM playback_events pe
                LEFT JOIN flashcard_packs fp ON fp.id = pe.flashcard_pack_id
                WHERE pe.user_id = :profile_id
                  AND pe.content_type = 'flashcards'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY fp.title, pe.flashcard_mode
                ORDER BY plays DESC, last_played DESC
            ", array_merge([':profile_id' => $selectedProfileId], $params));

            $selectedProfileTables['quickCalm'] = bbapp_query_rows($pdo, "
                SELECT COALESCE(qc.title, pe.quick_calm_mode, 'Unknown mode') AS title,
                       COUNT(*) AS plays,
                       MIN(pe.created_at) AS first_played,
                       MAX(pe.created_at) AS last_played
                FROM playback_events pe
                LEFT JOIN quick_calm qc ON qc.id = pe.quick_calm_id
                WHERE pe.user_id = :profile_id
                  AND pe.content_type = 'quick_calm'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY 1
                ORDER BY plays DESC, last_played DESC
            ", array_merge([':profile_id' => $selectedProfileId], $params));

            $selectedProfileTables['recentEvents'] = bbapp_query_rows($pdo, "
                SELECT
                    pe.content_type,
                    pe.event_type,
                    COALESCE(
                        v.title,
                        st.title,
                        fp.title,
                        CASE
                            WHEN pe.content_type = 'flashcards' AND pe.flashcard_mode = 'all-available' THEN 'All available cards'
                            WHEN pe.content_type = 'quick_calm' THEN COALESCE(qc.title, pe.quick_calm_mode, 'Unknown mode')
                            ELSE 'Unknown content'
                        END
                    ) AS title,
                    pe.created_at
                FROM playback_events pe
                LEFT JOIN videos v ON v.id = pe.video_id
                LEFT JOIN sound_tracks st ON st.id = pe.sound_track_id
                LEFT JOIN flashcard_packs fp ON fp.id = pe.flashcard_pack_id
                LEFT JOIN quick_calm qc ON qc.id = pe.quick_calm_id
                WHERE pe.user_id = :profile_id" . $dateFilter . "
                ORDER BY pe.created_at DESC
                LIMIT 50
            ", array_merge([':profile_id' => $selectedProfileId], $params));
        }
    }

    return [
        'range' => $range,
        'generatedAt' => (new DateTimeImmutable())->format('d M Y, H:i'),
        'kpis' => [
            'totalUsers' => $totalUsers,
            'newToday' => bbapp_query_value($pdo, "
                SELECT COUNT(*)
                FROM profiles
                WHERE " . $nonAdminProfileFilter . "
                  AND created_at >= CURRENT_DATE
            "),
            'new7Days' => bbapp_query_value($pdo, "
                SELECT COUNT(*)
                FROM profiles
                WHERE " . $nonAdminProfileFilter . "
                  AND created_at >= CURRENT_DATE - INTERVAL '6 days'
            "),
            'new30Days' => bbapp_query_value($pdo, "
                SELECT COUNT(*)
                FROM profiles
                WHERE " . $nonAdminProfileFilter . "
                  AND created_at >= CURRENT_DATE - INTERVAL '29 days'
            "),
            'active24h' => $active24h,
            'active7Days' => bbapp_query_value($pdo, "
                SELECT COUNT(DISTINCT pe.user_id)
                FROM playback_events pe
                " . $playbackProfileJoin . "
                WHERE pe.created_at >= CURRENT_DATE - INTERVAL '6 days'
            "),
            'active30Days' => $active30Days,
            'effectivePremiumUsers' => $effectivePremiumUsers,
            'paidPremiumUsers' => $paidPremiumUsers,
            'premiumConversionRate' => $totalUsers > 0 ? round(($paidPremiumUsers / $totalUsers) * 100, 1) : 0,
            'stickinessRate' => $active30Days > 0 ? round(($active24h / $active30Days) * 100, 1) : 0,
            'playEventsInRange' => $playEventsInRange,
            'activeUsersInRange' => $activeUsersInRange,
            'avgEventsPerActiveUser' => $activeUsersInRange > 0 ? round($playEventsInRange / $activeUsersInRange, 1) : 0,
            'returningUsers' => bbapp_query_value($pdo, "
                SELECT COUNT(*)
                FROM (
                    SELECT pe.user_id
                    FROM playback_events pe
                    " . $playbackProfileJoin . "
                    GROUP BY user_id
                    HAVING COUNT(DISTINCT pe.created_at::date) > 1
                ) returning_users
            "),
            'usersNeverPlayed' => bbapp_query_value($pdo, "
                SELECT COUNT(*)
                FROM profiles p
                WHERE " . $nonAdminProfileAliasFilter . "
                  AND NOT EXISTS (
                    SELECT 1 FROM playback_events pe WHERE pe.user_id = p.id
                )
            "),
        ],
        'charts' => [
            'profiles' => [
                'labels' => $profileDaily['labels'],
                'newUsers' => $profileDaily['values'],
                'cumulativeUsers' => $profileCumulative,
            ],
            'dailyActiveUsers' => bbapp_series_from_rows($dayTemplate, $dauRows),
            'playbackByType' => $playbackByType,
            'themeMode' => bbapp_query_rows($pdo, "
                SELECT
                    CASE
                        WHEN nightmode IS TRUE THEN 'Dark mode'
                        WHEN nightmode IS FALSE THEN 'Light mode'
                        ELSE 'Unknown'
                    END AS label,
                    COUNT(*) AS value
                FROM user_settings us
                INNER JOIN profiles p ON p.id = us.user_id
                WHERE " . $nonAdminProfileAliasFilter . "
                GROUP BY 1
                ORDER BY 1
            "),
            'premiumStatus' => bbapp_query_rows($pdo, "
                SELECT
                    CASE
                        WHEN is_premium IS TRUE AND has_premium_override IS TRUE THEN 'Premium + override'
                        WHEN is_premium IS TRUE THEN 'Premium'
                        WHEN has_premium_override IS TRUE THEN 'Premium override'
                        ELSE 'Free'
                    END AS label,
                    COUNT(*) AS value
                FROM profiles
                WHERE " . $nonAdminProfileFilter . "
                GROUP BY 1
                ORDER BY 1
            "),
            'selectedProfile' => $selectedProfileCharts,
        ],
        'tables' => [
            'playbackTypes' => bbapp_query_rows($pdo, "
                SELECT pe.content_type, COUNT(*) AS plays, COUNT(DISTINCT pe.user_id) AS unique_users, MAX(pe.created_at) AS last_played
                FROM playback_events pe
                " . $playbackProfileJoin . "
                WHERE event_type = 'play'" . $joinedEventDateFilter . "
                GROUP BY pe.content_type
                ORDER BY plays DESC
            ", $params),
            'videos' => bbapp_query_rows($pdo, "
                SELECT COALESCE(v.title, 'Unknown video') AS title, COALESCE(v.is_premium, false) AS is_premium,
                       COUNT(pe.id) AS plays, COUNT(DISTINCT pe.user_id) AS unique_users, MAX(pe.created_at) AS last_played
                FROM playback_events pe
                " . $playbackProfileJoin . "
                LEFT JOIN videos v ON v.id = pe.video_id
                WHERE pe.content_type = 'video'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY v.title, v.is_premium
                ORDER BY plays DESC, title ASC
            ", $params),
            'sounds' => bbapp_query_rows($pdo, "
                SELECT COALESCE(st.title, 'Unknown sound') AS title, COALESCE(st.is_premium, false) AS is_premium,
                       COUNT(pe.id) AS plays, COUNT(DISTINCT pe.user_id) AS unique_users, MAX(pe.created_at) AS last_played
                FROM playback_events pe
                " . $playbackProfileJoin . "
                LEFT JOIN sound_tracks st ON st.id = pe.sound_track_id
                WHERE pe.content_type = 'sound'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY st.title, st.is_premium
                ORDER BY plays DESC, title ASC
            ", $params),
            'flashcards' => bbapp_query_rows($pdo, "
                SELECT
                    COALESCE(fp.title, CASE WHEN pe.flashcard_mode = 'all-available' THEN 'All available cards' ELSE 'Unknown pack' END) AS title,
                    COALESCE(fp.is_premium, false) AS is_premium,
                    COALESCE(pe.flashcard_mode, 'pack') AS mode,
                    COUNT(pe.id) AS plays,
                    COUNT(DISTINCT pe.user_id) AS unique_users,
                    MAX(pe.created_at) AS last_played
                FROM playback_events pe
                " . $playbackProfileJoin . "
                LEFT JOIN flashcard_packs fp ON fp.id = pe.flashcard_pack_id
                WHERE pe.content_type = 'flashcards'
                  AND pe.event_type = 'play'" . $dateFilter . "
                GROUP BY fp.title, fp.is_premium, pe.flashcard_mode
                ORDER BY plays DESC, title ASC
            ", $params),
            'quickCalm' => bbapp_query_rows($pdo, "
                SELECT COALESCE(qc.title, pe.quick_calm_mode, 'Unknown mode') AS title,
                       COUNT(*) AS plays,
                       COUNT(DISTINCT pe.user_id) AS unique_users,
                       MAX(pe.created_at) AS last_played
                FROM playback_events pe
                " . $playbackProfileJoin . "
                LEFT JOIN quick_calm qc ON qc.id = pe.quick_calm_id
                WHERE pe.content_type = 'quick_calm'
                  AND event_type = 'play'" . $joinedEventDateFilter . "
                GROUP BY 1
                ORDER BY plays DESC, title ASC
            ", $params),
            'contentHealth' => bbapp_query_rows($pdo, "
                SELECT 'Videos' AS content, COUNT(*) AS total,
                       COUNT(*) FILTER (WHERE is_published IS TRUE) AS published,
                       COUNT(*) FILTER (WHERE is_published IS NOT TRUE) AS unpublished,
                       COUNT(*) FILTER (WHERE is_premium IS TRUE) AS premium
                FROM videos
                UNION ALL
                SELECT 'Sounds' AS content, COUNT(*) AS total,
                       COUNT(*) FILTER (WHERE is_published IS TRUE) AS published,
                       COUNT(*) FILTER (WHERE is_published IS NOT TRUE) AS unpublished,
                       COUNT(*) FILTER (WHERE is_premium IS TRUE) AS premium
                FROM sound_tracks
                UNION ALL
                SELECT 'Flashcard packs' AS content, COUNT(*) AS total,
                       COUNT(*) FILTER (WHERE is_published IS TRUE) AS published,
                       COUNT(*) FILTER (WHERE is_published IS NOT TRUE) AS unpublished,
                       COUNT(*) FILTER (WHERE is_premium IS TRUE) AS premium
                FROM flashcard_packs
                UNION ALL
                SELECT 'Flashcards' AS content, COUNT(*) AS total,
                       COUNT(*) FILTER (WHERE is_published IS TRUE) AS published,
                       COUNT(*) FILTER (WHERE is_published IS NOT TRUE) AS unpublished,
                       NULL::bigint AS premium
                FROM flashcards
                ORDER BY content
            "),
            'zeroPlayContent' => bbapp_query_rows($pdo, "
                SELECT 'Video' AS type, v.title, v.is_premium, v.is_published
                FROM videos v
                WHERE NOT EXISTS (
                    SELECT 1 FROM playback_events pe
                    INNER JOIN profiles p ON p.id = pe.user_id
                    WHERE pe.video_id = v.id
                      AND pe.content_type = 'video'
                      AND pe.event_type = 'play'
                      AND " . $nonAdminProfileAliasFilter . "
                )
                UNION ALL
                SELECT 'Sound' AS type, st.title, st.is_premium, st.is_published
                FROM sound_tracks st
                WHERE NOT EXISTS (
                    SELECT 1 FROM playback_events pe
                    INNER JOIN profiles p ON p.id = pe.user_id
                    WHERE pe.sound_track_id = st.id
                      AND pe.content_type = 'sound'
                      AND pe.event_type = 'play'
                      AND " . $nonAdminProfileAliasFilter . "
                )
                UNION ALL
                SELECT 'Flashcard pack' AS type, fp.title, fp.is_premium, fp.is_published
                FROM flashcard_packs fp
                WHERE NOT EXISTS (
                    SELECT 1 FROM playback_events pe
                    INNER JOIN profiles p ON p.id = pe.user_id
                    WHERE pe.flashcard_pack_id = fp.id
                      AND pe.content_type = 'flashcards'
                      AND pe.event_type = 'play'
                      AND " . $nonAdminProfileAliasFilter . "
                )
                ORDER BY type, title
            "),
            'recentActivity' => bbapp_query_rows($pdo, "
                SELECT
                    RIGHT(pe.user_id::text, 6) AS user_suffix,
                    pe.content_type,
                    pe.event_type,
                    COALESCE(
                        v.title,
                        st.title,
                        fp.title,
                        CASE
                            WHEN pe.content_type = 'flashcards' AND pe.flashcard_mode = 'all-available' THEN 'All available cards'
                            WHEN pe.content_type = 'quick_calm' THEN COALESCE(qc.title, pe.quick_calm_mode, 'Unknown mode')
                            ELSE 'Unknown content'
                        END
                    ) AS title,
                    pe.created_at
                FROM playback_events pe
                " . $playbackProfileJoin . "
                LEFT JOIN videos v ON v.id = pe.video_id
                LEFT JOIN sound_tracks st ON st.id = pe.sound_track_id
                LEFT JOIN flashcard_packs fp ON fp.id = pe.flashcard_pack_id
                LEFT JOIN quick_calm qc ON qc.id = pe.quick_calm_id
                WHERE 1 = 1" . $dateFilter . "
                ORDER BY pe.created_at DESC
                LIMIT 30
            ", $params),
            'profileUsage' => $profileUsageRows,
            'selectedProfile' => $selectedProfileTables,
        ],
        'selectedProfile' => $selectedProfile,
    ];
}
