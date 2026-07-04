<?php
$title = "envs.net | IdleRPG";

/*
 * envs.net defaults:
 *
 * - Website page: /var/www/envs.net/idlerpg/index.php
 * - Bot default export: /srv/envsbot/envsbot/data/idlerpg/<room-slug>/
 *
 * The preferred setup is to let envsbot export directly below
 * /var/www/envs.net/idlerpg/data. The page still falls back to the bot runtime
 * export directory when the PHP/webserver user can read it.
 */
const IDLERPG_DEFAULT_ROOM_SLUG = 'idlerpg_at_conference.envs.net';
const IDLERPG_WEBSITE_EXPORT_BASE = '/var/www/envs.net/idlerpg/data';
const IDLERPG_BOT_EXPORT_BASE = '/srv/envsbot/envsbot/data/idlerpg';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function idlerpg_load_json($path, $default = []) {
    if (!is_readable($path)) {
        return $default;
    }
    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

function idlerpg_ttl($seconds) {
    $seconds = max(0, (int) $seconds);
    $days = intdiv($seconds, 86400);
    $seconds %= 86400;
    $hours = intdiv($seconds, 3600);
    $seconds %= 3600;
    $minutes = intdiv($seconds, 60);
    $seconds %= 60;
    return sprintf('%d days, %02d:%02d:%02d', $days, $hours, $minutes, $seconds);
}

function idlerpg_time_value($value) {
    if ($value === null || $value === '') {
        return '';
    }
    if (is_numeric($value)) {
        return date('Y-m-d H:i:s T', (int) $value);
    }
    return (string) $value;
}

function idlerpg_player_name($player) {
    return $player['character'] ?? $player['name'] ?? 'unknown';
}

function idlerpg_player_level($player) {
    return (int) ($player['level'] ?? $player['lvl'] ?? 0);
}

function idlerpg_player_class($player) {
    return $player['class'] ?? $player['char_class'] ?? 'idler';
}

function idlerpg_player_online($player) {
    if (isset($player['online'])) {
        return (bool) $player['online'];
    }
    if (isset($player['status'])) {
        return strtolower((string) $player['status']) === 'online';
    }
    return false;
}

function idlerpg_player_coord($player, $axis) {
    if (isset($player[$axis]) && is_numeric($player[$axis])) {
        return (float) $player[$axis];
    }

    foreach (['position', 'map', 'coords', 'coordinates'] as $field) {
        if (isset($player[$field]) && is_array($player[$field])) {
            if (isset($player[$field][$axis]) && is_numeric($player[$field][$axis])) {
                return (float) $player[$field][$axis];
            }
            if ($axis === 'x' && isset($player[$field][0]) && is_numeric($player[$field][0])) {
                return (float) $player[$field][0];
            }
            if ($axis === 'y' && isset($player[$field][1]) && is_numeric($player[$field][1])) {
                return (float) $player[$field][1];
            }
        }
    }

    return 0.0;
}

function idlerpg_point_coord($point, $axis) {
    if (!is_array($point)) {
        return 0.0;
    }
    if (isset($point[$axis]) && is_numeric($point[$axis])) {
        return (float) $point[$axis];
    }
    if ($axis === 'x' && isset($point[0]) && is_numeric($point[0])) {
        return (float) $point[0];
    }
    if ($axis === 'y' && isset($point[1]) && is_numeric($point[1])) {
        return (float) $point[1];
    }
    return 0.0;
}

function idlerpg_room_slug() {
    $env_slug = getenv('ENVS_IDLERPG_ROOM_SLUG');
    if ($env_slug !== false && trim($env_slug) !== '') {
        return trim($env_slug);
    }
    return IDLERPG_DEFAULT_ROOM_SLUG;
}

function idlerpg_candidate_dirs() {
    $slug = idlerpg_room_slug();
    $candidates = [];

    /*
     * ENVS_IDLERPG_DATA_DIR may point either to the room directory itself:
     *   /path/to/data/idlerpg_at_conference.envs.net
     * or to the export base:
     *   /path/to/data
     */
    $env_dir = getenv('ENVS_IDLERPG_DATA_DIR');
    if ($env_dir !== false && trim($env_dir) !== '') {
        $env_dir = rtrim(trim($env_dir), '/');
        $candidates[] = $env_dir;
        $candidates[] = $env_dir . '/' . $slug;
    }

    $env_base = getenv('ENVS_IDLERPG_EXPORT_BASE');
    if ($env_base !== false && trim($env_base) !== '') {
        $env_base = rtrim(trim($env_base), '/');
        $candidates[] = $env_base . '/' . $slug;
        $candidates[] = $env_base;
    }

    // Preferred website layout for envs.net.
    $candidates[] = __DIR__ . '/data/' . $slug;
    $candidates[] = IDLERPG_WEBSITE_EXPORT_BASE . '/' . $slug;

    // Bot default export layout for envs.net. This requires readable/traversable
    // permissions for the PHP/webserver user.
    $candidates[] = IDLERPG_BOT_EXPORT_BASE . '/' . $slug;

    // Fallbacks for local testing or manually copied JSON files.
    $candidates[] = __DIR__ . '/data';
    $candidates[] = IDLERPG_WEBSITE_EXPORT_BASE;
    $candidates[] = IDLERPG_BOT_EXPORT_BASE;
    $candidates[] = __DIR__;

    return array_values(array_unique($candidates));
}

function idlerpg_has_readable_export($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    foreach (['room.json', 'map.json', 'leaderboard.json', 'players.json'] as $file) {
        if (is_readable(rtrim($dir, '/') . '/' . $file)) {
            return true;
        }
    }
    return false;
}

function idlerpg_data_dir() {
    foreach (idlerpg_candidate_dirs() as $candidate) {
        if (idlerpg_has_readable_export($candidate)) {
            return $candidate;
        }
    }
    return IDLERPG_BOT_EXPORT_BASE . '/' . idlerpg_room_slug();
}

function idlerpg_data_file($filename) {
    return rtrim(idlerpg_data_dir(), '/') . '/' . ltrim($filename, '/');
}

function idlerpg_sort_players($players) {
    usort($players, function ($a, $b) {
        $level_cmp = idlerpg_player_level($b) <=> idlerpg_player_level($a);
        if ($level_cmp !== 0) {
            return $level_cmp;
        }
        return ((int) ($a['ttl'] ?? 0)) <=> ((int) ($b['ttl'] ?? 0));
    });
    return $players;
}

function idlerpg_current_view() {
    $allowed = ['home', 'players', 'map', 'quest', 'events', 'hof', 'commands'];
    $view = strtolower(trim((string) ($_GET['view'] ?? 'home')));
    return in_array($view, $allowed, true) ? $view : 'home';
}

function idlerpg_view_url($view, $extra = []) {
    $params = array_merge(['view' => $view], $extra);
    return '?' . http_build_query($params);
}

function idlerpg_player_url($name) {
    return idlerpg_view_url('players', ['character' => $name]);
}

function idlerpg_map_percent($value, $max) {
    return max(0, min(100, ($value / max(1, $max)) * 100));
}

function idlerpg_achievement_count($player) {
    return is_array($player['achievements'] ?? null) ? count($player['achievements']) : 0;
}

function idlerpg_event_time($event) {
    $ts = (int) ($event['ts'] ?? 0);
    if ($ts <= 0) {
        return '';
    }
    return date('Y-m-d H:i', $ts);
}

function idlerpg_event_matches_player($event, $character) {
    $character = strtolower(trim((string) $character));
    if ($character === '') {
        return false;
    }
    $players = is_array($event['players'] ?? null) ? $event['players'] : [];
    foreach ($players as $player) {
        if (strtolower((string) $player) === $character) {
            return true;
        }
    }
    return stripos((string) ($event['text'] ?? ''), $character) !== false;
}

function idlerpg_render_events($events, $limit = 10) {
    $items = array_slice($events, 0, max(0, (int) $limit));
    if (count($items) === 0) {
        echo '<p class="muted">No recent events yet.</p>';
        return;
    }
    echo '<ol class="idlerpg-events">';
    foreach ($items as $event) {
        echo '<li>';
        echo '<span class="event-time">' . e(idlerpg_event_time($event)) . '</span> ';
        echo '<span class="event-kind">[' . e($event['kind'] ?? 'event') . ']</span> ';
        echo e($event['text'] ?? '');
        echo '</li>';
    }
    echo '</ol>';
}

$data_dir = idlerpg_data_dir();
$leaderboard_payload = idlerpg_load_json(idlerpg_data_file('leaderboard.json'), ['players' => []]);
$players_payload = idlerpg_load_json(idlerpg_data_file('players.json'), ['players' => []]);
$map_payload = idlerpg_load_json(idlerpg_data_file('map.json'), ['players' => [], 'width' => 500, 'height' => 500]);
$hof_payload = idlerpg_load_json(idlerpg_data_file('hall_of_fame.json'), ['seasons' => []]);
$events_payload = idlerpg_load_json(idlerpg_data_file('events.json'), ['events' => []]);

$leaderboard = is_array($leaderboard_payload['players'] ?? null) ? $leaderboard_payload['players'] : [];
$players = is_array($players_payload['players'] ?? null) ? $players_payload['players'] : $leaderboard;
$players = idlerpg_sort_players($players);
if (count($leaderboard) === 0 && count($players) > 0) {
    $leaderboard = $players;
}
$map_players = is_array($map_payload['players'] ?? null) ? $map_payload['players'] : $players;
if (count($map_players) === 0 && count($players) > 0) {
    $map_players = $players;
}
$seasons = is_array($hof_payload['seasons'] ?? null) ? $hof_payload['seasons'] : [];
$events = is_array($events_payload['events'] ?? null) ? $events_payload['events'] : [];
usort($events, function ($a, $b) {
    return ((int) ($b['ts'] ?? 0)) <=> ((int) ($a['ts'] ?? 0));
});
$room = $leaderboard_payload['room'] ?? $players_payload['room'] ?? $map_payload['room'] ?? '';
$updated = $leaderboard_payload['generated_at'] ?? $players_payload['generated_at'] ?? $map_payload['generated_at'] ?? null;
$selected_character = trim((string) ($_GET['character'] ?? ''));
$selected_profile = null;
foreach ($players as $player) {
    if (strcasecmp(idlerpg_player_name($player), $selected_character) === 0) {
        $selected_profile = $player;
        break;
    }
}
$view = idlerpg_current_view();
$quest = is_array($map_payload['quest'] ?? null) ? $map_payload['quest'] : null;
$map_width = max(1, (int) ($map_payload['width'] ?? $map_payload['map_x'] ?? 500));
$map_height = max(1, (int) ($map_payload['height'] ?? $map_payload['map_y'] ?? 500));
$online_count = 0;
foreach ($players as $player) {
    if (idlerpg_player_online($player)) {
        $online_count++;
    }
}

include '../neoenvs_header.php';
?>

<body id="body">

<!-- Back button -->
<nav class="sidenav">
    <a href="/" title="Back to envs.net">
        <img src="/img/envs_logo_200x200.png" class="site-icon" alt="envs.net">
    </a>
</nav>

<style>
.idlerpg-page .lead,
.idlerpg-page .section-text,
.idlerpg-page .command-list,
.idlerpg-page .tips-list,
.idlerpg-page .admin-list {
    max-width: 96ch;
}

.idlerpg-titlebar {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 2ch;
    max-width: 110ch;
}

.idlerpg-titlebar h1 {
    margin-bottom: .25em;
}

.idlerpg-subnav {
    display: flex;
    flex-wrap: wrap;
    gap: .5ch 1ch;
    margin: .5em 0 1.5em;
    max-width: 110ch;
}

.idlerpg-subnav a {
    border: 1px solid var(--c-lines, currentColor);
    padding: .25em 1ch;
    text-decoration: none;
    background: transparent;
    color: var(--c-link, #0aa);
    box-shadow: inset 0 0 0 0 var(--c-lines, currentColor);
    transition:
        background-color .12s ease-in-out,
        color .12s ease-in-out,
        box-shadow .12s ease-in-out,
        transform .08s ease-in-out;
}

.idlerpg-subnav a:hover,
.idlerpg-subnav a:focus {
    background: color-mix(in srgb, var(--c-link, #0aa) 18%, transparent);
    box-shadow: inset 0 -2px 0 0 var(--c-link, #0aa);
    color: var(--c-fg, inherit);
    outline: none;
}

.idlerpg-subnav a:active {
    transform: translateY(1px);
    box-shadow: inset 0 0 0 999px color-mix(in srgb, var(--c-link, #0aa) 28%, transparent);
}

.idlerpg-subnav a.active,
.idlerpg-subnav a.active:hover,
.idlerpg-subnav a.active:focus {
    background: var(--c-link, #0aa);
    border-color: var(--c-link, #0aa);
    color: var(--c-bg, #000);
    box-shadow:
        inset 0 -2px 0 0 var(--c-fg, currentColor),
        0 0 0 1px var(--c-link, #0aa);
}

.idlerpg-stats {
    display: grid;
    gap: 1em;
    grid-template-columns: repeat(auto-fit, minmax(14em, 1fr));
    max-width: 96ch;
    margin: 1em 0 1.5em;
}

.idlerpg-stat {
    border-left: 4px solid var(--c-lines, currentColor);
    padding: .5em 0 .5em 2ch;
}

.idlerpg-stat strong {
    display: block;
    font-size: 1.35em;
}

.idlerpg-intro {
    max-width: 96ch;
    margin: 1em 0 1.5em;
}

.idlerpg-intro .steps {
    display: grid;
    gap: 1em;
    grid-template-columns: repeat(auto-fit, minmax(18em, 1fr));
    margin: 1em 0;
}

.idlerpg-intro .step {
    border-left: 4px solid var(--c-lines, currentColor);
    padding: .5em 0 .5em 2ch;
}

.idlerpg-intro .step strong {
    display: block;
    margin-bottom: .25em;
}

.idlerpg-explain {
    max-width: 96ch;
    margin: 1.5em 0;
}

.idlerpg-explain-grid {
    display: grid;
    gap: 1em;
    grid-template-columns: repeat(auto-fit, minmax(20em, 1fr));
    margin: 1em 0;
}

.idlerpg-explain-card {
    border-left: 4px solid var(--c-lines, currentColor);
    padding: .5em 0 .5em 2ch;
}

.idlerpg-explain-card h3 {
    margin-top: 0;
}

.idlerpg-page table {
    width: 100%;
    max-width: 110ch;
    margin-bottom: 2em;
}

.idlerpg-page th,
.idlerpg-page td {
    border-bottom: 1px solid var(--c-lines, currentColor);
    padding: .25em .5ch .25em 0;
    text-align: left;
    vertical-align: top;
}

.idlerpg-page code,
.idlerpg-sidebar code {
    white-space: nowrap;
}

.idlerpg-page .muted,
.idlerpg-sidebar .muted {
    opacity: .75;
}

.idlerpg-page .warning {
    border-left: 4px solid var(--c-lines, currentColor);
    padding: .5em 0 .5em 2ch;
    max-width: 96ch;
}

.idlerpg-map-wrap {
    max-width: 760px;
    margin: 1em 0 1.5em;
}

.idlerpg-world-map {
    display: block;
    width: min(100%, 720px);
    height: auto;
    border: 1px solid var(--c-lines, currentColor);
    background: #f3edbd;
}

.idlerpg-map-label {
    font-family: inherit;
    font-style: italic;
    font-size: 16px;
    fill: #4b2d10;
    opacity: .88;
}

.idlerpg-map-small-label {
    font-family: inherit;
    font-size: 11px;
    fill: #4b2d10;
    opacity: .8;
}

.idlerpg-map-marker text {
    font-family: inherit;
    font-size: 12px;
}

.idlerpg-map-marker.online circle {
    fill: #2f80ff;
}

.idlerpg-map-marker.offline circle {
    fill: #b33;
}

.idlerpg-map-marker circle {
    stroke: #111;
    stroke-width: 1.5;
}

.idlerpg-map-marker text {
    fill: #111;
    paint-order: stroke;
    stroke: #f3edbd;
    stroke-width: 4px;
    stroke-linejoin: round;
}

.idlerpg-map-quest {
    fill: #d99b00;
    stroke: #111;
    stroke-width: 1.5;
}

.idlerpg-map-quest-line {
    fill: none;
    stroke: #d99b00;
    stroke-width: 2;
    stroke-dasharray: 6 5;
}

.idlerpg-profile-grid {
    display: grid;
    gap: 1em;
    grid-template-columns: repeat(auto-fit, minmax(22em, 1fr));
    max-width: 110ch;
}

.idlerpg-card {
    border-left: 4px solid var(--c-lines, currentColor);
    padding: .5em 0 .5em 2ch;
}

.idlerpg-card h3 {
    margin-top: 0;
}

.idlerpg-events {
    max-width: 110ch;
    padding-left: 2em;
}

.idlerpg-events li {
    margin-bottom: .5em;
}

.idlerpg-events .event-time,
.idlerpg-events .event-kind {
    opacity: .75;
}

.idlerpg-items-table td.unique {
    font-style: italic;
}

.idlerpg-sidebar .box {
    border-left: 4px solid var(--c-lines, currentColor);
    margin-bottom: 1em;
    padding: .5em 0 .5em 2ch;
}

.idlerpg-sidebar ul {
    padding-left: 1.4em;
}

.idlerpg-sidebar li {
    margin-bottom: .4em;
}

.idlerpg-sidebar code {
    white-space: normal;
    overflow-wrap: anywhere;
}

@media (max-width: 720px) {
    .idlerpg-titlebar {
        display: block;
    }
}
</style>

<main class="content idlerpg-page">
    <div class="idlerpg-titlebar">
        <h1>IdleRPG</h1>
        <p class="muted">
            <?php echo e(count($players)); ?> players · <?php echo e($online_count); ?> online
        </p>
    </div>

    <nav class="idlerpg-subnav" aria-label="IdleRPG navigation">
        <?php
        $tabs = [
            'home' => 'Home',
            'players' => 'Player Info',
            'quest' => 'Quest Info',
            'events' => 'Events',
            'map' => 'World Map',
            'hof' => 'Hall of Fame',
            'commands' => 'Commands',
        ];
        ?>
        <?php foreach ($tabs as $tab => $label): ?>
            <a class="<?php echo $view === $tab ? 'active' : ''; ?>" href="<?php echo e(idlerpg_view_url($tab)); ?>"><?php echo e($label); ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($room !== ''): ?>
        <p class="muted">
            Room: <code><?php echo e($room); ?></code>
            <?php if ($updated): ?> · updated <?php echo e(idlerpg_time_value($updated)); ?><?php endif; ?>
        </p>
    <?php else: ?>
        <p class="warning">
            No readable exported game data found yet. The page tried the envs.net defaults:
            <code><?php echo e(__DIR__ . '/data/' . idlerpg_room_slug()); ?></code>,
            <code><?php echo e(IDLERPG_WEBSITE_EXPORT_BASE . '/' . idlerpg_room_slug()); ?></code>
            and
            <code><?php echo e(IDLERPG_BOT_EXPORT_BASE . '/' . idlerpg_room_slug()); ?></code>.
            Use <code>?debug=1</code> for a readable-path check.
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['debug'])): ?>
        <h2>Export debug</h2>
        <p class="section-text">
            Selected data directory:
            <code><?php echo e($data_dir); ?></code>
        </p>
        <table>
            <thead><tr><th>Candidate</th><th>Directory</th><th>map.json</th><th>leaderboard.json</th><th>players.json</th></tr></thead>
            <tbody>
                <?php foreach (idlerpg_candidate_dirs() as $candidate): ?>
                    <tr>
                        <td><code><?php echo e($candidate); ?></code></td>
                        <td><?php echo is_dir($candidate) ? 'yes' : 'no'; ?></td>
                        <td><?php echo is_readable(rtrim($candidate, '/') . '/map.json') ? 'readable' : 'not readable'; ?></td>
                        <td><?php echo is_readable(rtrim($candidate, '/') . '/leaderboard.json') ? 'readable' : 'not readable'; ?></td>
                        <td><?php echo is_readable(rtrim($candidate, '/') . '/players.json') ? 'readable' : 'not readable'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="section-text">
            Server-side checks for the bot default path:
            <code>sudo -u www-data test -r <?php echo e(IDLERPG_BOT_EXPORT_BASE . '/' . idlerpg_room_slug() . '/map.json'); ?> && echo readable</code>
            and
            <code>namei -l <?php echo e(IDLERPG_BOT_EXPORT_BASE . '/' . idlerpg_room_slug() . '/map.json'); ?></code>.
        </p>
    <?php endif; ?>

    <?php if ($view === 'home'): ?>
        <section class="idlerpg-intro">
            <p class="lead">
                IdleRPG is a slow multiplayer role-playing game for the envs.net XMPP chat.
                You create a character, stay logged in, idle in the room and wait for your
                next level. The trick is simple: the less you do, the better you progress.
            </p>

            <p>
                Everything happens automatically. Your character gains levels while you are
                logged in, can find items, fight other players, join quests and wander over
                the world map. Normal room messages, logouts and unlucky events add time to
                your level clock. Battles, godsends, quests and items can remove time again.
            </p>

            <div class="steps">
                <div class="step">
                    <strong>1. Create a character</strong>
                    <code>,idlerpg register &lt;name&gt; &lt;class&gt;</code>
                </div>
                <div class="step">
                    <strong>2. Log in</strong>
                    <code>,idlerpg login</code>
                </div>
                <div class="step">
                    <strong>3. Idle quietly</strong>
                    Talking adds penalties; waiting lowers your clock.
                </div>
                <div class="step">
                    <strong>4. Watch the world</strong>
                    Check rankings, quests, achievements and the map.
                </div>
            </div>
        </section>

        <section class="idlerpg-explain">
            <h2>How the game works</h2>

            <div class="idlerpg-explain-grid">
                <article class="idlerpg-explain-card">
                    <h3>Levelling</h3>
                    <p>
                        Your next-level timer counts down while you are logged in.
                        The default formula is <code>600 * 1.16^level</code>, so
                        higher levels take longer.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Penalties</h3>
                    <p>
                        IdleRPG rewards idling. Talking in the game room, logging out
                        or being hit by unlucky events adds time to your clock.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Items and battles</h3>
                    <p>
                        Items increase your item sum. In battles, both players roll
                        against their item sum. Winning can remove time; losing can add
                        time. Critical strikes and item drops can happen too.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Alignment</h3>
                    <p>
                        Characters can be <code>good</code>, <code>neutral</code> or
                        <code>evil</code>. Alignment can influence random events and
                        group bonuses.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Quests</h3>
                    <p>
                        Quests pick a group of experienced players and send them on an
                        automatic journey. Completing a quest reduces the participants'
                        remaining time to level.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>World map</h3>
                    <p>
                        The map is a virtual <code>500 x 500</code> world. Players move
                        automatically. A position like <code>[293,133] lv.16</code>
                        means x=293, y=133 and level 16.
                    </p>
                </article>
            </div>
        </section>

        <div class="idlerpg-stats">
            <div class="idlerpg-stat">
                <span>Top player</span>
                <strong><?php echo count($leaderboard) > 0 ? e(idlerpg_player_name($leaderboard[0])) : 'n/a'; ?></strong>
            </div>
            <div class="idlerpg-stat">
                <span>Highest level</span>
                <strong><?php echo count($leaderboard) > 0 ? 'lv.' . e(idlerpg_player_level($leaderboard[0])) : 'n/a'; ?></strong>
            </div>
            <div class="idlerpg-stat">
                <span>Quest</span>
                <strong><?php echo $quest ? 'active' : 'none'; ?></strong>
            </div>
        </div>

        <h2>Top players</h2>
        <?php if (count($leaderboard) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Character</th>
                        <th>Class</th>
                        <th>Level</th>
                        <th>Next level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($leaderboard, 0, 10) as $index => $player): ?>
                        <?php $name = idlerpg_player_name($player); ?>
                        <tr>
                            <td><?php echo e($player['rank'] ?? ($index + 1)); ?></td>
                            <td><a href="<?php echo e(idlerpg_player_url($name)); ?>"><?php echo e($name); ?></a></td>
                            <td><?php echo e(idlerpg_player_class($player)); ?></td>
                            <td>lv.<?php echo e(idlerpg_player_level($player)); ?></td>
                            <td><?php echo e(idlerpg_ttl($player['ttl'] ?? 0)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">No public leaderboard has been exported yet. In chat, use <code>,idlerpg top</code>.</p>
        <?php endif; ?>

        <h2>Recent events</h2>
        <?php idlerpg_render_events($events, 8); ?>
    <?php endif; ?>

    <?php if ($view === 'players'): ?>
        <h2><?php echo $selected_profile ? 'Player profile' : 'Pick a player to view'; ?></h2>

        <?php if ($selected_profile): ?>
            <div class="idlerpg-profile-grid">
                <div class="idlerpg-card">
                    <h3><?php echo e(idlerpg_player_name($selected_profile)); ?></h3>
                    <table>
                        <tbody>
                            <tr><th>Class</th><td><?php echo e(idlerpg_player_class($selected_profile)); ?></td></tr>
                            <tr><th>Title</th><td><?php echo e($selected_profile['title'] ?? ''); ?></td></tr>
                            <tr><th>Level</th><td>lv.<?php echo e(idlerpg_player_level($selected_profile)); ?></td></tr>
                            <tr><th>Next level</th><td><?php echo e(idlerpg_ttl($selected_profile['ttl'] ?? 0)); ?></td></tr>
                            <tr><th>Alignment</th><td><?php echo e($selected_profile['alignment'] ?? 'neutral'); ?></td></tr>
                            <tr><th>Map</th><td>[<?php echo e((int) idlerpg_player_coord($selected_profile, 'x')); ?>,<?php echo e((int) idlerpg_player_coord($selected_profile, 'y')); ?>]</td></tr>
                            <tr><th>Item sum</th><td><?php echo e($selected_profile['item_sum'] ?? 0); ?></td></tr>
                            <tr><th>Status</th><td><?php echo idlerpg_player_online($selected_profile) ? 'online' : 'offline'; ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="idlerpg-card">
                    <h3>Achievements</h3>
                    <?php $achievements = is_array($selected_profile['achievements'] ?? null) ? $selected_profile['achievements'] : []; ?>
                    <?php if (count($achievements) > 0): ?>
                        <ul>
                            <?php foreach ($achievements as $achievement): ?>
                                <li><strong><?php echo e($achievement['title'] ?? $achievement['key'] ?? 'achievement'); ?></strong><?php if (!empty($achievement['description'])): ?> — <?php echo e($achievement['description']); ?><?php endif; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="muted">No achievements yet.</p>
                    <?php endif; ?>
                </div>

                <div class="idlerpg-card">
                    <h3>Items</h3>
                    <?php $items = is_array($selected_profile['items'] ?? null) ? $selected_profile['items'] : []; ?>
                    <?php $unique_items = is_array($selected_profile['unique_items'] ?? null) ? $selected_profile['unique_items'] : []; ?>
                    <?php if (count($items) > 0): ?>
                        <table class="idlerpg-items-table">
                            <tbody>
                                <?php foreach ($items as $item_name => $item_level): ?>
                                    <tr>
                                        <th><?php echo e($item_name); ?></th>
                                        <td>lv.<?php echo e($item_level); ?></td>
                                        <td class="unique"><?php echo e($unique_items[$item_name] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="muted">No items yet.</p>
                    <?php endif; ?>
                </div>

                <div class="idlerpg-card">
                    <h3>Recent player events</h3>
                    <?php
                    $player_events = array_values(array_filter($events, function ($event) use ($selected_profile) {
                        return idlerpg_event_matches_player($event, idlerpg_player_name($selected_profile));
                    }));
                    idlerpg_render_events($player_events, 8);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($players) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Player</th>
                        <th>Class</th>
                        <th>Level</th>
                        <th>Next level</th>
                        <th>Achievements</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $index => $player): ?>
                        <?php $name = idlerpg_player_name($player); ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td><a href="<?php echo e(idlerpg_player_url($name)); ?>"><?php echo e($name); ?></a></td>
                            <td><?php echo e(idlerpg_player_class($player)); ?></td>
                            <td>lv.<?php echo e(idlerpg_player_level($player)); ?></td>
                            <td><?php echo e(idlerpg_ttl($player['ttl'] ?? 0)); ?></td>
                            <td><?php echo e(idlerpg_achievement_count($player)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">No players yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($view === 'quest'): ?>
        <h2>Current Quest</h2>
        <?php if ($quest): ?>
            <p class="section-text">
                <strong>Quest:</strong>
                <?php echo e($quest['text'] ?? $quest['description'] ?? 'adventure'); ?>
            </p>
            <?php if (!empty($quest['route']) && is_array($quest['route'])): ?>
                <p class="section-text">
                    <strong>Current goal:</strong>
                    <?php
                    $last_point = end($quest['route']);
                    reset($quest['route']);
                    echo '[' . e((int) idlerpg_point_coord($last_point, 'x')) . ',' . e((int) idlerpg_point_coord($last_point, 'y')) . ']';
                    ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($quest['participants']) && is_array($quest['participants'])): ?>
                <table>
                    <thead><tr><th>#</th><th>Participant</th></tr></thead>
                    <tbody>
                        <?php foreach ($quest['participants'] as $index => $participant): ?>
                            <?php $participant_name = is_array($participant) ? idlerpg_player_name($participant) : (string) $participant; ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><a href="<?php echo e(idlerpg_player_url($participant_name)); ?>"><?php echo e($participant_name); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">No active quest right now.</p>
        <?php endif; ?>

        <h2>Quest Map</h2>
        <?php $show_map_only = true; ?>
    <?php endif; ?>

    <?php if ($view === 'map' || ($view === 'quest' && !empty($show_map_only))): ?>
        <h2><?php echo $view === 'map' ? 'World Map' : 'Quest Map'; ?></h2>
        <p class="section-text muted">
            Offline users are red, online users are blue, quest points are orange.
        </p>
        <?php if (count($map_players) > 0): ?>
            <div class="idlerpg-map-wrap">
                <svg class="idlerpg-world-map" viewBox="0 0 <?php echo e($map_width); ?> <?php echo e($map_height); ?>" role="img" aria-label="IdleRPG world map">
                    <defs>
                        <pattern id="idlerpgNoise" width="32" height="32" patternUnits="userSpaceOnUse">
                            <path d="M0 8 L8 0 M20 32 L32 20 M4 28 L28 4 M16 18 L18 16" stroke="#8a5a20" stroke-width="1" opacity=".28"/>
                        </pattern>
                        <filter id="idlerpgRough">
                            <feTurbulence type="fractalNoise" baseFrequency="0.018" numOctaves="3" seed="7"/>
                            <feDisplacementMap in="SourceGraphic" scale="2"/>
                        </filter>
                    </defs>

                    <rect x="0" y="0" width="<?php echo e($map_width); ?>" height="<?php echo e($map_height); ?>" fill="#f4edbd"/>
                    <rect x="0" y="0" width="<?php echo e($map_width); ?>" height="<?php echo e($map_height); ?>" fill="url(#idlerpgNoise)" opacity=".45"/>

                    <path d="M0,0 L145,0 C85,35 50,70 0,95 Z" fill="#8a4f12" opacity=".9" filter="url(#idlerpgRough)"/>
                    <path d="M0,345 C85,315 135,345 176,393 C110,415 62,462 0,500 Z" fill="#8a4f12" opacity=".85" filter="url(#idlerpgRough)"/>
                    <path d="M355,500 C415,430 455,395 500,370 L500,500 Z" fill="#8a4f12" opacity=".9" filter="url(#idlerpgRough)"/>
                    <path d="M270,45 C315,20 365,28 388,74 C362,116 315,136 270,115 C245,85 246,58 270,45 Z" fill="#a57937" opacity=".42" filter="url(#idlerpgRough)"/>
                    <path d="M292,230 C330,208 371,218 395,254 C365,285 316,293 282,265 C272,250 276,238 292,230 Z" fill="#7d4d1b" opacity=".55" filter="url(#idlerpgRough)"/>
                    <path d="M230,380 C270,332 318,350 348,403 C318,437 258,445 220,413 Z" fill="#7d4d1b" opacity=".62" filter="url(#idlerpgRough)"/>

                    <text class="idlerpg-map-label" x="27" y="36" transform="rotate(-7 27 36)">Debmark</text>
                    <text class="idlerpg-map-label" x="286" y="42" transform="rotate(-8 286 42)">Mountains of</text>
                    <text class="idlerpg-map-label" x="300" y="64" transform="rotate(-8 300 64)">Qwok</text>
                    <text class="idlerpg-map-label" x="382" y="93" transform="rotate(8 382 93)">The land of</text>
                    <text class="idlerpg-map-label" x="399" y="118" transform="rotate(8 399 118)">Qwok</text>
                    <text class="idlerpg-map-label" x="90" y="160" transform="rotate(-5 90 160)">Jow Boti</text>
                    <text class="idlerpg-map-label" x="82" y="182" transform="rotate(-5 82 182)">Territory</text>
                    <text class="idlerpg-map-label" x="365" y="218" transform="rotate(-3 365 218)">Velbragh</text>
                    <text class="idlerpg-map-small-label" x="40" y="255" transform="rotate(-5 40 255)">Secret Passage</text>
                    <text class="idlerpg-map-small-label" x="50" y="275" transform="rotate(-5 50 275)">to Aharah</text>
                    <text class="idlerpg-map-label" x="4" y="374" transform="rotate(-5 4 374)">The great</text>
                    <text class="idlerpg-map-label" x="3" y="397" transform="rotate(-5 3 397)">Shell</text>
                    <text class="idlerpg-map-label" x="3" y="420" transform="rotate(-5 3 420)">mountains</text>
                    <text class="idlerpg-map-label" x="270" y="390" transform="rotate(-5 270 390)">Tower of</text>
                    <text class="idlerpg-map-label" x="270" y="415" transform="rotate(-5 270 415)">Anh-Allor</text>
                    <text class="idlerpg-map-label" x="410" y="468" transform="rotate(-5 410 468)">Irnalveh</text>

                    <?php if ($quest && is_array($quest['route'] ?? null) && count($quest['route']) > 0): ?>
                        <?php
                        $route_points = [];
                        foreach ($quest['route'] as $point) {
                            $route_points[] = (int) idlerpg_point_coord($point, 'x') . ',' . (int) idlerpg_point_coord($point, 'y');
                        }
                        ?>
                        <polyline class="idlerpg-map-quest-line" points="<?php echo e(implode(' ', $route_points)); ?>"/>
                        <?php foreach ($quest['route'] as $idx => $point): ?>
                            <?php $qx = idlerpg_point_coord($point, 'x'); $qy = idlerpg_point_coord($point, 'y'); ?>
                            <g>
                                <rect class="idlerpg-map-quest" x="<?php echo e($qx - 5); ?>" y="<?php echo e($qy - 5); ?>" width="10" height="10"/>
                                <text x="<?php echo e($qx + 7); ?>" y="<?php echo e($qy - 7); ?>" class="idlerpg-map-small-label">Q<?php echo e($idx + 1); ?></text>
                            </g>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php foreach (array_slice($map_players, 0, 120) as $player): ?>
                        <?php
                        $name = idlerpg_player_name($player);
                        $x = max(0, min($map_width, idlerpg_player_coord($player, 'x')));
                        $y = max(0, min($map_height, idlerpg_player_coord($player, 'y')));
                        $class = idlerpg_player_online($player) ? 'idlerpg-map-marker online' : 'idlerpg-map-marker offline';
                        ?>
                        <a href="<?php echo e(idlerpg_player_url($name)); ?>">
                            <g class="<?php echo e($class); ?>">
                                <circle cx="<?php echo e($x); ?>" cy="<?php echo e($y); ?>" r="4"/>
                                <text x="<?php echo e($x + 7); ?>" y="<?php echo e($y - 7); ?>"><?php echo e($name); ?></text>
                            </g>
                        </a>
                    <?php endforeach; ?>
                </svg>
            </div>

            <h3>Map positions</h3>
            <table>
                <thead><tr><th>Character</th><th>Position</th><th>Level</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($map_players, 0, 25) as $player): ?>
                        <?php $name = idlerpg_player_name($player); ?>
                        <tr>
                            <td><a href="<?php echo e(idlerpg_player_url($name)); ?>"><?php echo e($name); ?></a></td>
                            <td>[<?php echo e((int) idlerpg_player_coord($player, 'x')); ?>,<?php echo e((int) idlerpg_player_coord($player, 'y')); ?>]</td>
                            <td>lv.<?php echo e(idlerpg_player_level($player)); ?></td>
                            <td><?php echo idlerpg_player_online($player) ? 'online' : 'offline'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">No readable map data found. The website needs <code>map.json</code> or <code>players.json</code> in a readable export directory.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($view === 'events'): ?>
        <h2>Recent Events</h2>
        <p class="section-text muted">Public game history for level-ups, battles, items, quests, seasons and other room events.</p>
        <?php idlerpg_render_events($events, 50); ?>
    <?php endif; ?>

    <?php if ($view === 'hof'): ?>
        <h2>Hall of Fame</h2>
        <?php if (count($seasons) > 0): ?>
            <table>
                <thead><tr><th>Season</th><th>Champion</th><th>Ended</th></tr></thead>
                <tbody>
                    <?php foreach (array_reverse($seasons) as $season): ?>
                        <tr>
                            <td><?php echo e($season['id'] ?? '?'); ?></td>
                            <td><?php echo e($season['champion'] ?? ''); ?></td>
                            <td><?php echo !empty($season['ended_at']) ? e(idlerpg_time_value($season['ended_at'])) : ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">No completed seasons yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($view === 'commands'): ?>
        <h2>Commands</h2>
        <ul class="command-list">
            <li><code>,idlerpg register &lt;character&gt; &lt;class&gt;</code> — create a character</li>
            <li><code>,idlerpg login</code> / <code>,idlerpg logout</code> — start or stop idling</li>
            <li><code>,idlerpg status [character]</code> — show character progress</li>
            <li><code>,idlerpg profile [character]</code> — show a detailed profile</li>
            <li><code>,idlerpg achievements [character]</code> — show achievements</li>
            <li><code>,idlerpg title &lt;achievement|none&gt;</code> — choose a public title</li>
            <li><code>,idlerpg top</code> / <code>,idlerpg players</code> — show rankings and players</li>
            <li><code>,idlerpg events</code> — show recent game events</li>
            <li><code>,idlerpg map</code> / <code>,idlerpg hof</code> / <code>,idlerpg season</code> — show map, Hall of Fame and season state</li>
        </ul>
    <?php endif; ?>
</main>

<aside id="sidebar" class="idlerpg-sidebar">
    <div class="box">
        <h2>Quick start</h2>
        <ul>
            <li><code>,idlerpg register &lt;name&gt; &lt;class&gt;</code></li>
            <li><code>,idlerpg login</code></li>
            <li><code>,idlerpg status</code></li>
            <li><code>,idlerpg top</code></li>
        </ul>
    </div>

    <div class="box">
        <h2>Navigation</h2>
        <ul>
            <li><a href="<?php echo e(idlerpg_view_url('players')); ?>">Player Info</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('quest')); ?>">Quest Info</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('events')); ?>">Events</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('map')); ?>">World Map</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('hof')); ?>">Hall of Fame</a></li>
        </ul>
    </div>

    <div class="box">
        <h2>Map legend</h2>
        <p class="muted">
            Blue = online, red = offline, orange = quest point.
            <code>[293,133] lv.16</code> means x=293, y=133 and level 16.
        </p>
    </div>

</aside>

<?php include '../neoenvs_footer.php'; ?>
