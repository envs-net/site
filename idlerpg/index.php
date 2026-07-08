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
        $timestamp = (int) $value;
        if ($timestamp <= 0) {
            return '';
        }
        return date('Y-m-d H:i:s T', $timestamp);
    }
    return (string) $value;
}

function idlerpg_season_end_value($season) {
    if (!is_array($season) || count($season) === 0) {
        return '';
    }
    $ends_at = $season['ends_at'] ?? '';
    $label = idlerpg_time_value($ends_at);
    return $label !== '' ? $label : 'manual';
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
    $allowed = ['home', 'players', 'map', 'quest', 'events', 'items', 'achievements', 'rules', 'hof', 'commands'];
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

function idlerpg_map_marker_label_layout($x, $y, $name, $map_width, $map_height) {
    $margin = 6;
    $label_gap = 7;
    $label_width = min(max(24, strlen((string) $name) * 7), max(24, $map_width - ($margin * 2)));

    if ($x + $label_gap + $label_width <= $map_width - $margin) {
        $label_x = $x + $label_gap;
        $anchor = 'start';
    } elseif ($x - $label_gap - $label_width >= $margin) {
        $label_x = $x - $label_gap;
        $anchor = 'end';
    } else {
        $label_x = max($margin + ($label_width / 2), min($map_width - $margin - ($label_width / 2), $x));
        $anchor = 'middle';
    }

    $label_y = $y - $label_gap;
    if ($label_y < 14) {
        $label_y = $y + 16;
    }
    $label_y = max(14, min($map_height - $margin, $label_y));

    return [
        'x' => $label_x,
        'y' => $label_y,
        'anchor' => $anchor,
    ];
}

function idlerpg_achievement_count($player) {
    return is_array($player['achievements'] ?? null) ? count($player['achievements']) : 0;
}

function idlerpg_player_created_at($player) {
    if (isset($player['created_at']) && is_numeric($player['created_at'])) {
        return max(0, (int) $player['created_at']);
    }
    if (isset($player['registered_at']) && is_numeric($player['registered_at'])) {
        return max(0, (int) $player['registered_at']);
    }
    return 0;
}

function idlerpg_player_played_seconds($player) {
    if (isset($player['played_for']) && is_numeric($player['played_for'])) {
        return max(0, (int) $player['played_for']);
    }
    $created_at = idlerpg_player_created_at($player);
    return $created_at > 0 ? max(0, time() - $created_at) : 0;
}

function idlerpg_player_played_label($player) {
    $seconds = idlerpg_player_played_seconds($player);
    return $seconds > 0 ? idlerpg_seconds_label($seconds) : '';
}

function idlerpg_player_created_label($player) {
    $created_at = idlerpg_player_created_at($player);
    return $created_at > 0 ? idlerpg_time_value($created_at) : '';
}

function idlerpg_event_time($event) {
    $ts = (int) ($event['ts'] ?? 0);
    if ($ts <= 0) {
        return '';
    }
    return date('Y-m-d H:i', $ts);
}

function idlerpg_event_icon($event) {
    $kind = strtolower((string) ($event['kind'] ?? 'event'));
    $text = strtolower((string) ($event['text'] ?? ''));

    if (str_contains($kind, 'achievement') || str_contains($text, 'achievement')) {
        return '🏅';
    }
    if (str_contains($kind, 'level') || str_contains($text, 'level')) {
        return '🏆';
    }
    if (
        str_contains($kind, 'duel')
        || str_contains($kind, 'battle')
        || str_contains($kind, 'combat')
        || str_contains($text, 'duel')
        || str_contains($text, 'combat')
    ) {
        return '⚔️';
    }
    if (str_contains($kind, 'critical') || str_contains($text, 'critical')) {
        return '💥';
    }
    if (str_contains($kind, 'item') || str_contains($kind, 'unique') || str_contains($text, 'found')) {
        return '🎒';
    }
    if (str_contains($kind, 'godsend') || str_contains($kind, 'bless')) {
        return '✨';
    }
    if (str_contains($kind, 'calamity') || str_contains($kind, 'curse')) {
        return '💀';
    }
    if (str_contains($kind, 'quest')) {
        return '🧭';
    }
    if (str_contains($kind, 'season')) {
        return '🏁';
    }
    if (str_contains($kind, 'admin')) {
        return '🛠️';
    }
    if (str_contains($kind, 'register') || str_contains($kind, 'login') || str_contains($kind, 'logout')) {
        return '👤';
    }

    return '•';
}

function idlerpg_collect_unique_items($players) {
    $unique_items = [];

    foreach ($players as $player) {
        $holder = idlerpg_player_name($player);
        $items = is_array($player['items'] ?? null) ? $player['items'] : [];
        $uniques = is_array($player['unique_items'] ?? null) ? $player['unique_items'] : [];

        foreach ($uniques as $slot => $unique_name) {
            if (is_array($unique_name)) {
                $name = $unique_name['name'] ?? $unique_name['title'] ?? $slot;
                $level = $unique_name['level'] ?? ($items[$slot] ?? '');
            } else {
                $name = (string) $unique_name;
                $level = $items[$slot] ?? '';
            }

            if (trim((string) $name) === '') {
                continue;
            }

            $unique_items[] = [
                'holder' => $holder,
                'slot' => (string) $slot,
                'name' => $name,
                'level' => $level,
            ];
        }
    }

    usort($unique_items, function ($a, $b) {
        $level_cmp = ((int) ($b['level'] ?? 0)) <=> ((int) ($a['level'] ?? 0));
        if ($level_cmp !== 0) {
            return $level_cmp;
        }
        return strcasecmp((string) $a['name'], (string) $b['name']);
    });

    return $unique_items;
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

function idlerpg_event_kind_value($event) {
    return strtolower(trim((string) ($event['kind'] ?? 'event')));
}

function idlerpg_event_matches_type($event, $type) {
    $type = strtolower(trim((string) $type));
    if ($type === '' || $type === 'all') {
        return true;
    }
    $kind = idlerpg_event_kind_value($event);
    $text = strtolower((string) ($event['text'] ?? ''));
    return $kind === $type || str_contains($kind, $type) || str_contains($text, $type);
}

function idlerpg_int_param($name, $default = 1, $min = 1, $max = 999) {
    $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
    if ($value === false || $value === null) {
        return $default;
    }
    return max($min, min($max, (int) $value));
}

function idlerpg_rule_source($room_payload) {
    foreach (['rules', 'config', 'settings'] as $key) {
        if (isset($room_payload[$key]) && is_array($room_payload[$key])) {
            return $room_payload[$key];
        }
    }
    return [];
}

function idlerpg_rule_value($rules, $key, $default) {
    return array_key_exists($key, $rules) ? $rules[$key] : $default;
}

function idlerpg_bool_label($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'yes' : 'no';
}

function idlerpg_seconds_label($seconds) {
    $seconds = max(0, (int) $seconds);
    if ($seconds === 0) {
        return '0s';
    }

    $parts = [];
    $days = intdiv($seconds, 86400);
    $seconds %= 86400;
    $hours = intdiv($seconds, 3600);
    $seconds %= 3600;
    $minutes = intdiv($seconds, 60);
    $seconds %= 60;

    if ($days > 0) {
        $parts[] = $days . 'd';
    }
    if ($hours > 0) {
        $parts[] = $hours . 'h';
    }
    if ($minutes > 0) {
        $parts[] = $minutes . 'm';
    }
    if ($seconds > 0 || count($parts) === 0) {
        $parts[] = $seconds . 's';
    }

    return implode(' ', $parts);
}

function idlerpg_percent_label($value) {
    if (!is_numeric($value)) {
        return (string) $value;
    }
    $number = (float) $value;
    if ($number > 0 && $number <= 1) {
        $number *= 100;
    }
    return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.') . '%';
}

function idlerpg_weight_label($value) {
    if (!is_numeric($value)) {
        return (string) $value;
    }
    return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
}

function idlerpg_filtered_events($events, $type = '', $player = '') {
    $player = trim((string) $player);
    return array_values(array_filter($events, function ($event) use ($type, $player) {
        if (!idlerpg_event_matches_type($event, $type)) {
            return false;
        }
        if ($player !== '' && !idlerpg_event_matches_player($event, $player)) {
            return false;
        }
        return true;
    }));
}

function idlerpg_render_pager($view, $page, $total, $per_page, $extra = []) {
    $pages = max(1, (int) ceil($total / max(1, $per_page)));
    if ($pages <= 1) {
        return;
    }
    echo '<p class="idlerpg-pager">';
    if ($page > 1) {
        echo '<a href="' . e(idlerpg_view_url($view, array_merge($extra, ['page' => $page - 1]))) . '">previous</a> ';
    }
    echo '<span>page ' . e($page) . ' / ' . e($pages) . '</span>';
    if ($page < $pages) {
        echo ' <a href="' . e(idlerpg_view_url($view, array_merge($extra, ['page' => $page + 1]))) . '">next</a>';
    }
    echo '</p>';
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
        echo '<span class="event-icon">' . e(idlerpg_event_icon($event)) . '</span> ';
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
$achievements_payload = idlerpg_load_json(idlerpg_data_file('achievements.json'), ['achievements' => []]);
$room_payload = idlerpg_load_json(idlerpg_data_file('room.json'), []);

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
$achievement_catalog = is_array($achievements_payload['achievements'] ?? null) ? $achievements_payload['achievements'] : [];
if (count($achievement_catalog) === 0 && is_array($room_payload['achievement_catalog'] ?? null)) {
    $achievement_catalog = $room_payload['achievement_catalog'];
}
$event_types = array_values(array_unique(array_filter(array_map('idlerpg_event_kind_value', $events))));
sort($event_types);
$event_filter_type = trim((string) ($_GET['type'] ?? ''));
$event_filter_player = trim((string) ($_GET['player'] ?? ''));
$event_page = idlerpg_int_param('page', 1, 1, 999);
$event_per_page = 25;
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
$unique_items = idlerpg_collect_unique_items($players);
$season = is_array($room_payload['season'] ?? null) ? $room_payload['season'] : [];
$season_id = $season['id'] ?? 'n/a';
$season_started = idlerpg_time_value($season['started_at'] ?? '');
$season_ends = idlerpg_season_end_value($season);
$players_total = (int) ($room_payload['players_total'] ?? count($players));
$players_online = (int) ($room_payload['players_online'] ?? $online_count);
$events_total = count($events);
$map_size = $map_width . ' x ' . $map_height;
$rule_source = idlerpg_rule_source($room_payload);
$rules = [
    'tick_seconds' => idlerpg_rule_value($rule_source, 'tick_seconds', 60),
    'rp_base' => idlerpg_rule_value($rule_source, 'rp_base', 600),
    'rp_step' => idlerpg_rule_value($rule_source, 'rp_step', 1.16),
    'penalty_step' => idlerpg_rule_value($rule_source, 'penalty_step', 1.14),
    'message_penalty' => idlerpg_rule_value($rule_source, 'message_penalty', 1),
    'logout_penalty' => idlerpg_rule_value($rule_source, 'logout_penalty', 20),
    'logout_grace_seconds' => idlerpg_rule_value($rule_source, 'logout_grace_seconds', 300),
    'max_penalty' => idlerpg_rule_value($rule_source, 'max_penalty', 604800),
    'count_command_messages' => idlerpg_rule_value($rule_source, 'count_command_messages', false),
    'map_x' => idlerpg_rule_value($rule_source, 'map_x', $map_width),
    'map_y' => idlerpg_rule_value($rule_source, 'map_y', $map_height),
    'map_step_per_second' => idlerpg_rule_value($rule_source, 'map_step_per_second', idlerpg_rule_value($rule_source, 'map_step_per_tick', 1)),
    'map_step_per_tick' => idlerpg_rule_value($rule_source, 'map_step_per_tick', idlerpg_rule_value($rule_source, 'map_step_per_second', 1)),
    'grid_battle_enabled' => idlerpg_rule_value($rule_source, 'grid_battle_enabled', true),
    'quest_grid_step_seconds' => idlerpg_rule_value($rule_source, 'quest_grid_step_seconds', 2),
    'announce_login' => idlerpg_rule_value($rule_source, 'announce_login', true),
    'announce_top_interval' => idlerpg_rule_value($rule_source, 'announce_top_interval', 21600),
    'announce_top_limit' => idlerpg_rule_value($rule_source, 'announce_top_limit', 5),
    'update_room_topic' => idlerpg_rule_value($rule_source, 'update_room_topic', false),
    'topic_update_interval' => idlerpg_rule_value($rule_source, 'topic_update_interval', 14400),
    'topic_custom_text' => idlerpg_rule_value($rule_source, 'topic_custom_text', ''),
    'event_chance' => idlerpg_rule_value($rule_source, 'event_chance', 0.01),
    'item_chance' => idlerpg_rule_value($rule_source, 'item_chance', 0.20),
    'battle_event_weight' => idlerpg_rule_value($rule_source, 'battle_event_weight', 0.55),
    'team_battle_event_weight' => idlerpg_rule_value($rule_source, 'team_battle_event_weight', 0.08),
    'item_event_weight' => idlerpg_rule_value($rule_source, 'item_event_weight', 0.15),
    'item_damage_event_weight' => idlerpg_rule_value($rule_source, 'item_damage_event_weight', 0.08),
    'item_steal_event_weight' => idlerpg_rule_value($rule_source, 'item_steal_event_weight', 0.04),
    'alignment_event_weight' => idlerpg_rule_value($rule_source, 'alignment_event_weight', 0.10),
    'critical_strike_chance' => idlerpg_rule_value($rule_source, 'critical_strike_chance', 1 / 35),
    'critical_strike_chance_good' => idlerpg_rule_value($rule_source, 'critical_strike_chance_good', 1 / 50),
    'critical_strike_chance_evil' => idlerpg_rule_value($rule_source, 'critical_strike_chance_evil', 1 / 20),
    'item_drop_chance' => idlerpg_rule_value($rule_source, 'item_drop_chance', 0.02),
    'level_battle_chance_below_25' => idlerpg_rule_value($rule_source, 'level_battle_chance_below_25', 0.25),
    'level_battle_chance_at_25' => idlerpg_rule_value($rule_source, 'level_battle_chance_at_25', 1.0),
    'manual_duel_max_distance' => idlerpg_rule_value($rule_source, 'manual_duel_max_distance', 10),
    'manual_duel_cooldown_seconds' => idlerpg_rule_value($rule_source, 'manual_duel_cooldown_seconds', 3600),
    'battle_win_min_percent' => idlerpg_rule_value($rule_source, 'battle_win_min_percent', 7),
    'battle_loss_min_percent' => idlerpg_rule_value($rule_source, 'battle_loss_min_percent', 7),
    'critical_min_percent' => idlerpg_rule_value($rule_source, 'critical_min_percent', 5),
    'critical_max_percent' => idlerpg_rule_value($rule_source, 'critical_max_percent', 25),
    'godsend_min_percent' => idlerpg_rule_value($rule_source, 'godsend_min_percent', 5),
    'godsend_max_percent' => idlerpg_rule_value($rule_source, 'godsend_max_percent', 12),
    'calamity_min_percent' => idlerpg_rule_value($rule_source, 'calamity_min_percent', 5),
    'calamity_max_percent' => idlerpg_rule_value($rule_source, 'calamity_max_percent', 12),
    'alignment_bonus_percent' => idlerpg_rule_value($rule_source, 'alignment_bonus_percent', 7),
    'quest_reward_percent' => idlerpg_rule_value($rule_source, 'quest_reward_percent', 25),
    'team_battle_percent' => idlerpg_rule_value($rule_source, 'team_battle_percent', 20),
    'unique_items_enabled' => idlerpg_rule_value($rule_source, 'unique_items_enabled', true),
    'unique_item_min_level' => idlerpg_rule_value($rule_source, 'unique_item_min_level', 25),
    'unique_item_chance' => idlerpg_rule_value($rule_source, 'unique_item_chance', 0.025),
    'level_reward_min_level' => idlerpg_rule_value($rule_source, 'level_reward_min_level', 50),
    'quest_min_level' => idlerpg_rule_value($rule_source, 'quest_min_level', 40),
    'quest_min_online_seconds' => idlerpg_rule_value($rule_source, 'quest_min_online_seconds', 36000),
    'quest_time_enabled' => idlerpg_rule_value($rule_source, 'quest_time_enabled', true),
    'quest_grid_enabled' => idlerpg_rule_value($rule_source, 'quest_grid_enabled', true),
    'quest_time_weight' => idlerpg_rule_value($rule_source, 'quest_time_weight', 0.5),
    'quest_grid_weight' => idlerpg_rule_value($rule_source, 'quest_grid_weight', 0.5),
    'quest_time_min_duration' => idlerpg_rule_value($rule_source, 'quest_time_min_duration', 43200),
    'quest_time_max_duration' => idlerpg_rule_value($rule_source, 'quest_time_max_duration', 86400),
    'quest_interval' => idlerpg_rule_value($rule_source, 'quest_interval', 21600),
    'quest_min_duration' => idlerpg_rule_value($rule_source, 'quest_min_duration', 43200),
    'quest_max_duration' => idlerpg_rule_value($rule_source, 'quest_max_duration', 86400),
    'season_enabled' => idlerpg_rule_value($rule_source, 'season_enabled', false),
    'season_duration_days' => idlerpg_rule_value($rule_source, 'season_duration_days', 90),
    'season_reset_on_rollover' => idlerpg_rule_value($rule_source, 'season_reset_on_rollover', false),
    'season_hof_size' => idlerpg_rule_value($rule_source, 'season_hof_size', 10),
    'season_achievement_gates_enabled' => idlerpg_rule_value($rule_source, 'season_achievement_gates_enabled', true),
    'event_log_limit' => idlerpg_rule_value($rule_source, 'event_log_limit', 200),
    'event_retention_days' => idlerpg_rule_value($rule_source, 'event_retention_days', 90),
    'export_event_limit' => idlerpg_rule_value($rule_source, 'export_event_limit', 50),
    'export_top_limit' => idlerpg_rule_value($rule_source, 'export_top_limit', 50),
];
$show_hof = filter_var($rules['season_enabled'], FILTER_VALIDATE_BOOLEAN) || count($seasons) > 0;
if ($view === 'hof' && !$show_hof) {
    $view = 'home';
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

<link rel="stylesheet" href="style.css">

<main class="content idlerpg-page">
    <div class="idlerpg-titlebar">
        <h1>IdleRPG</h1>
        <p class="muted">
            <?php echo e($players_total); ?> players · <?php echo e($players_online); ?> online
        </p>
    </div>

    <nav class="idlerpg-subnav" aria-label="IdleRPG navigation">
        <?php
        $tabs = [
            'home' => 'Home',
            'players' => 'Player Info',
            'quest' => 'Quest Info',
            'events' => 'Events',
            'items' => 'Items',
            'achievements' => 'Achievements',
            'rules' => 'Rules',
            'map' => 'World Map',
            'commands' => 'Commands',
        ];
        if ($show_hof) {
            $tabs['hof'] = 'Hall of Fame';
        }
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
                logged in, can find items, fight other players, challenge nearby players to
                manual duels, join quests and wander over the world map. Normal room messages,
                logouts and unlucky events add time to
                your level clock. Battles, godsends, quests and items can remove time again.
            </p>

            <p>
                This IdleRPG is provided by the <strong>envsbot</strong> IdleRPG plugin.
                The bot and plugin source are available on
                <a href="https://github.com/envs-net/envsbot">GitHub</a>.
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
                <span>Season</span>
                <strong><?php echo e($season_id); ?></strong>
            </div>
            <div class="idlerpg-stat">
                <span>Quest</span>
                <strong><?php echo $quest ? 'active' : 'none'; ?></strong>
            </div>
            <div class="idlerpg-stat">
                <span>Events</span>
                <strong><?php echo e($events_total); ?></strong>
            </div>
        </div>

        <h2>Room status</h2>
        <table class="idlerpg-room-status">
            <tbody>
                <tr><td>Room</td><td><code><?php echo e($room !== '' ? $room : 'unknown'); ?></code></td></tr>
                <tr><td>Season</td><td><?php echo e($season_id); ?><?php if ($season_started !== ''): ?> · started <?php echo e($season_started); ?><?php endif; ?><?php if ($season_ends !== ''): ?> · ends <?php echo e($season_ends); ?><?php endif; ?></td></tr>
                <tr><td>Players</td><td><?php echo e($players_total); ?> total · <?php echo e($players_online); ?> online</td></tr>
                <tr><td>Map</td><td><?php echo e($map_size); ?></td></tr>
                <tr><td>Events</td><td><?php echo e($events_total); ?> exported public events</td></tr>
            </tbody>
        </table>

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

        <h2>Unique items</h2>
        <?php if (count($unique_items) > 0): ?>
            <table class="idlerpg-unique-items">
                <thead><tr><th>Item</th><th>Holder</th><th>Slot</th><th>Level</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($unique_items, 0, 5) as $item): ?>
                        <tr>
                            <td class="unique"><?php echo e($item['name']); ?></td>
                            <td><a href="<?php echo e(idlerpg_player_url($item['holder'])); ?>"><?php echo e($item['holder']); ?></a></td>
                            <td><?php echo e($item['slot']); ?></td>
                            <td><?php echo $item['level'] !== '' ? 'lv.' . e($item['level']) : ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><a href="<?php echo e(idlerpg_view_url('items')); ?>">Show all unique items</a></p>
        <?php else: ?>
            <p class="muted">No unique items have been found yet.</p>
        <?php endif; ?>

        <section class="idlerpg-explain">
            <h2>How the game works</h2>

            <div class="idlerpg-explain-grid">
                <article class="idlerpg-explain-card">
                    <h3>Levelling</h3>
                    <p>
                        Your next-level timer counts down while you are logged in.
                        The default formula is <code>600 * 1.16^level</code> through level 60;
                        after that, each additional level adds one day to the level-60 timer.
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
                        Items increase your item sum. Random battles and manual duels use
                        both players' item sum for their rolls. Winning can remove time; losing
                        can add time. Critical strikes, item drops, item damage and rare fair item
                        swaps can happen too. Rare unique artifacts can appear from level
                        <?php echo e($rules['unique_item_min_level']); ?>.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Manual duels</h3>
                    <p>
                        Use <code>,idlerpg duel &lt;character&gt;</code> to challenge a nearby
                        online player. The default range is <?php echo e($rules['manual_duel_max_distance']); ?> map units
                        and both duelists get a <?php echo e(idlerpg_seconds_label($rules['manual_duel_cooldown_seconds'])); ?> cooldown.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Alignment</h3>
                    <p>
                        Characters can be <code>good</code>, <code>neutral</code> or
                        <code>evil</code>. Alignment can influence random events,
                        group bonuses and party-wide blessings.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>Quests</h3>
                    <p>
                        Quests pick a group of experienced players from level
                        <?php echo e($rules['quest_min_level']); ?> who have been online for at least
                        <?php echo e(idlerpg_seconds_label($rules['quest_min_online_seconds'])); ?> and send them on an
                        automatic journey. Completing a quest reduces the participants'
                        remaining time to level.
                    </p>
                </article>

                <article class="idlerpg-explain-card">
                    <h3>World map</h3>
                    <p>
                        The map is a virtual <code>500 x 500</code> world. Players move
                        automatically once per simulated second, classic IdleRPG-style. A position like <code>[293,133] lv.16</code>
                        means x=293, y=133 and level 16.
                    </p>
                </article>
            </div>
        </section>

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
                            <tr><th>Playing since</th><td><?php echo e(idlerpg_player_created_label($selected_profile) !== '' ? idlerpg_player_created_label($selected_profile) : 'unknown'); ?></td></tr>
                            <tr><th>Playing for</th><td><?php echo e(idlerpg_player_played_label($selected_profile) !== '' ? idlerpg_player_played_label($selected_profile) : 'unknown'); ?></td></tr>
                            <tr><th>Idled online</th><td><?php echo e(idlerpg_seconds_label($selected_profile['idled'] ?? 0)); ?></td></tr>
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
                    <p><a href="<?php echo e(idlerpg_view_url('events', ['player' => idlerpg_player_name($selected_profile)])); ?>">Show all events for this player</a></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($players) > 0): ?>
            <table class="idlerpg-player-table">
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
        <p class="section-text muted">
            Quests are reserved for experienced characters. Players need at least
            level <?php echo e($rules['quest_min_level']); ?> and <?php echo e(idlerpg_seconds_label($rules['quest_min_online_seconds'])); ?> online time to be selected for a quest.
        </p>
        <?php if ($quest): ?>
            <?php
            $quest_type = strtolower((string) ($quest['type'] ?? ''));
            if ($quest_type === '') {
                $quest_type = !empty($quest['route']) ? 'grid' : 'time';
            }
            $quest_complete_at = (int) ($quest['complete_at'] ?? 0);
            $quest_started_at = (int) ($quest['started_at'] ?? 0);
            $quest_remaining = $quest_complete_at > 0 ? max(0, $quest_complete_at - time()) : 0;
            ?>
            <p class="section-text">
                <strong>Quest:</strong>
                <?php echo e($quest['text'] ?? $quest['description'] ?? 'adventure'); ?>
            </p>
            <table class="idlerpg-room-status">
                <tbody>
                    <tr><td>Type</td><td><?php echo e($quest_type === 'time' ? 'time-based' : 'grid-based'); ?></td></tr>
                    <?php if ($quest_started_at > 0): ?><tr><td>Started</td><td><?php echo e(idlerpg_time_value($quest_started_at)); ?></td></tr><?php endif; ?>
                    <?php if ($quest_complete_at > 0): ?><tr><td>Deadline</td><td><?php echo e(idlerpg_time_value($quest_complete_at)); ?></td></tr><?php endif; ?>
                    <?php if ($quest_complete_at > 0): ?><tr><td>Time left</td><td><?php echo e(idlerpg_seconds_label($quest_remaining)); ?></td></tr><?php endif; ?>
                    <?php if ($quest_type === 'time'): ?>
                        <tr><td>Rule</td><td>No quester may receive a penalty before the timer ends.</td></tr>
                    <?php elseif (is_array($quest['current_target'] ?? null)): ?>
                        <tr><td>Current target</td><td>[<?php echo e((int) idlerpg_point_coord($quest['current_target'], 'x')); ?>,<?php echo e((int) idlerpg_point_coord($quest['current_target'], 'y')); ?>]</td></tr>
                    <?php elseif (!empty($quest['route']) && is_array($quest['route'])): ?>
                        <?php $route_index = max(0, (int) ($quest['route_index'] ?? 0)); $target = $quest['route'][min($route_index, count($quest['route']) - 1)] ?? null; ?>
                        <?php if (is_array($target)): ?><tr><td>Current target</td><td>[<?php echo e((int) idlerpg_point_coord($target, 'x')); ?>,<?php echo e((int) idlerpg_point_coord($target, 'y')); ?>]</td></tr><?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php $questers = is_array($quest['questers'] ?? null) ? $quest['questers'] : (is_array($quest['participants'] ?? null) ? $quest['participants'] : []); ?>
            <?php if (count($questers) > 0): ?>
                <table>
                    <thead><tr><th>#</th><th>Participant</th></tr></thead>
                    <tbody>
                        <?php foreach ($questers as $index => $participant): ?>
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

        <?php $show_map_only = true; ?>
    <?php endif; ?>

    <?php if ($view === 'map' || ($view === 'quest' && !empty($show_map_only))): ?>
        <h2><?php echo $view === 'map' ? 'World Map' : 'Quest Map'; ?></h2>
        <p class="section-text muted">
            Offline users are red, online users are blue, quest points are orange. Manual duels
            are possible when two online players are close enough on the map.
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
                        $raw_x = max(0, min($map_width, idlerpg_player_coord($player, 'x')));
                        $raw_y = max(0, min($map_height, idlerpg_player_coord($player, 'y')));
                        $x = max(6, min($map_width - 6, $raw_x));
                        $y = max(6, min($map_height - 6, $raw_y));
                        $label = idlerpg_map_marker_label_layout($x, $y, $name, $map_width, $map_height);
                        $class = idlerpg_player_online($player) ? 'idlerpg-map-marker online' : 'idlerpg-map-marker offline';
                        ?>
                        <a href="<?php echo e(idlerpg_player_url($name)); ?>">
                            <g class="<?php echo e($class); ?>">
                                <title><?php echo e($name); ?> [<?php echo e((int) $raw_x); ?>,<?php echo e((int) $raw_y); ?>]</title>
                                <circle cx="<?php echo e($x); ?>" cy="<?php echo e($y); ?>" r="4"/>
                                <text x="<?php echo e($label['x']); ?>" y="<?php echo e($label['y']); ?>" text-anchor="<?php echo e($label['anchor']); ?>"><?php echo e($name); ?></text>
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

    <?php if ($view === 'items'): ?>
        <h2>Unique Items</h2>
        <p class="section-text muted">
            Unique items and artifacts currently held by players. Unique artifacts can only appear
            after level <?php echo e($rules['unique_item_min_level']); ?>.
        </p>
        <?php if (count($unique_items) > 0): ?>
            <table class="idlerpg-unique-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Holder</th>
                        <th>Slot</th>
                        <th>Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unique_items as $item): ?>
                        <tr>
                            <td class="unique"><?php echo e($item['name']); ?></td>
                            <td><a href="<?php echo e(idlerpg_player_url($item['holder'])); ?>"><?php echo e($item['holder']); ?></a></td>
                            <td><?php echo e($item['slot']); ?></td>
                            <td><?php echo $item['level'] !== '' ? 'lv.' . e($item['level']) : ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">No unique items have been found yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($view === 'events'): ?>
        <h2>Recent Events</h2>
        <p class="section-text muted">Public game history for level-ups, battles, items, quests, seasons and other room events.</p>
        <?php
        $filtered_events = idlerpg_filtered_events($events, $event_filter_type, $event_filter_player);
        $event_offset = ($event_page - 1) * $event_per_page;
        ?>
        <form class="idlerpg-filter" method="get">
            <input type="hidden" name="view" value="events">
            <label><span>Type</span>
                <select name="type">
                    <option value="">all</option>
                    <?php foreach ($event_types as $type): ?>
                        <option value="<?php echo e($type); ?>" <?php echo $event_filter_type === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><span>Player</span>
                <input type="text" name="player" value="<?php echo e($event_filter_player); ?>" placeholder="character">
            </label>
            <button type="submit">Apply filter</button>
            <?php if ($event_filter_type !== '' || $event_filter_player !== ''): ?>
                <a class="filter-reset" href="<?php echo e(idlerpg_view_url('events')); ?>">clear</a>
            <?php endif; ?>
        </form>
        <p class="idlerpg-filter-summary muted">
            Showing <?php echo e(count($filtered_events)); ?> of <?php echo e(count($events)); ?> exported events.
        </p>
        <?php
        idlerpg_render_events(array_slice($filtered_events, $event_offset, $event_per_page), $event_per_page);
        idlerpg_render_pager('events', $event_page, count($filtered_events), $event_per_page, ['type' => $event_filter_type, 'player' => $event_filter_player]);
        ?>
    <?php endif; ?>

    <?php if ($view === 'achievements'): ?>
        <h2>Achievements</h2>
        <p class="section-text muted">Available titles and long-term goals. The status column shows how many players have unlocked each achievement.</p>
        <?php if (count($achievement_catalog) > 0): ?>
            <table class="idlerpg-achievements">
                <thead><tr><th>Status</th><th>Key</th><th>Title</th><th>Description</th></tr></thead>
                <tbody>
                    <?php foreach ($achievement_catalog as $achievement): ?>
                        <?php
                        $key = (string) ($achievement['key'] ?? '');
                        $unlocked = 0;
                        foreach ($players as $player) {
                            $player_achievements = is_array($player['achievements'] ?? null) ? $player['achievements'] : [];
                            foreach ($player_achievements as $entry) {
                                $entry_key = is_array($entry) ? (string) ($entry['key'] ?? '') : (string) $entry;
                                if ($entry_key === $key) {
                                    $unlocked++;
                                    break;
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td class="status"><?php echo $unlocked > 0 ? '✅ ' . e($unlocked) : '▫️'; ?></td>
                            <td><code><?php echo e($key); ?></code></td>
                            <td><?php echo e($achievement['title'] ?? $key); ?></td>
                            <td><?php echo e($achievement['description'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">No achievement catalog has been exported yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($view === 'hof' && $show_hof): ?>
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

    <?php if ($view === 'rules'): ?>
        <section class="idlerpg-rules">
            <h2>Rules and Game Config</h2>
            <p class="section-text muted">
                This page documents the effective IdleRPG rule set shown by the public export.
                If the bot does not export a value yet, the website shows the current envsbot defaults.
            </p>

            <p class="idlerpg-formula">
                The most important rule: your level clock counts down only while you are logged in.
                Talking, logging out or unlucky events add time. Battles, quests, godsends and items
                can remove time again.
            </p>

            <div class="idlerpg-rules-grid">
                <article class="idlerpg-rule-card">
                    <h3>Timer and leveling</h3>
                    <table>
                        <tbody>
                            <tr><td>Tick interval</td><td><?php echo e(idlerpg_seconds_label($rules['tick_seconds'])); ?></td></tr>
                            <tr><td>Base timer</td><td><?php echo e(idlerpg_seconds_label($rules['rp_base'])); ?></td></tr>
                            <tr><td>Level scaling</td><td><code><?php echo e($rules['rp_step']); ?></code></td></tr>
                            <tr><td>Formula</td><td><code>TTL = <?php echo e($rules['rp_base']); ?> * <?php echo e($rules['rp_step']); ?>^level</code> through lv.60, then +1 day per level</td></tr>
                        </tbody>
                    </table>
                    <p class="muted">Classic IdleRPG uses exponential growth through level 60 and a linear one-day-per-level tail afterwards.</p>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Penalties</h3>
                    <table>
                        <tbody>
                            <tr><td>Message penalty base</td><td><?php echo e($rules['message_penalty']); ?>s per character before scaling</td></tr>
                            <tr><td>Penalty scaling</td><td><code><?php echo e($rules['penalty_step']); ?>^level</code></td></tr>
                            <tr><td>Logout penalty base</td><td><?php echo e($rules['logout_penalty']); ?>s before scaling</td></tr>
                            <tr><td>Logout grace</td><td><?php echo e(idlerpg_seconds_label($rules['logout_grace_seconds'])); ?></td></tr>
                            <tr><td>Single penalty cap</td><td><?php echo e(idlerpg_seconds_label($rules['max_penalty'])); ?></td></tr>
                            <tr><td>Commands count as messages</td><td><?php echo e(idlerpg_bool_label($rules['count_command_messages'])); ?></td></tr>
                        </tbody>
                    </table>
                    <p class="muted">Message penalties use message length, so longer messages hurt more.</p>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Random events</h3>
                    <table>
                        <tbody>
                            <tr><td>Event chance per tick</td><td><?php echo e(idlerpg_percent_label($rules['event_chance'])); ?></td></tr>
                            <tr><td>Battle weight</td><td><?php echo e(idlerpg_weight_label($rules['battle_event_weight'])); ?></td></tr>
                            <tr><td>Team battle weight</td><td><?php echo e(idlerpg_weight_label($rules['team_battle_event_weight'])); ?></td></tr>
                            <tr><td>Item event weight</td><td><?php echo e(idlerpg_weight_label($rules['item_event_weight'])); ?></td></tr>
                            <tr><td>Item damage weight</td><td><?php echo e(idlerpg_weight_label($rules['item_damage_event_weight'])); ?></td></tr>
                            <tr><td>Item swap weight</td><td><?php echo e(idlerpg_weight_label($rules['item_steal_event_weight'])); ?></td></tr>
                            <tr><td>Alignment event weight</td><td><?php echo e(idlerpg_weight_label($rules['alignment_event_weight'])); ?></td></tr>
                            <tr><td>Critical strike chance</td><td>neutral <?php echo e(idlerpg_percent_label($rules['critical_strike_chance'])); ?> · good <?php echo e(idlerpg_percent_label($rules['critical_strike_chance_good'])); ?> · evil <?php echo e(idlerpg_percent_label($rules['critical_strike_chance_evil'])); ?></td></tr>
                            <tr><td>Battle item steal chance</td><td><?php echo e(idlerpg_percent_label($rules['item_drop_chance'])); ?></td></tr>
                            <tr><td>Level-up battle chance</td><td>below lv.25 <?php echo e(idlerpg_percent_label($rules['level_battle_chance_below_25'])); ?> · lv.25+ <?php echo e(idlerpg_percent_label($rules['level_battle_chance_at_25'])); ?></td></tr>
                        </tbody>
                    </table>
                    <p class="muted">Weights are relative. Only events possible for the current player count are considered.</p>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Battle and event effects</h3>
                    <table>
                        <tbody>
                            <tr><td>Battle win minimum</td><td><?php echo e(idlerpg_percent_label($rules['battle_win_min_percent'])); ?> removed</td></tr>
                            <tr><td>Battle loss minimum</td><td><?php echo e(idlerpg_percent_label($rules['battle_loss_min_percent'])); ?> added</td></tr>
                            <tr><td>Manual duel max distance</td><td><?php echo e($rules['manual_duel_max_distance']); ?> map units</td></tr>
                            <tr><td>Manual duel cooldown</td><td><?php echo e(idlerpg_seconds_label($rules['manual_duel_cooldown_seconds'])); ?></td></tr>
                            <tr><td>Critical strike</td><td><?php echo e(idlerpg_percent_label($rules['critical_min_percent'])); ?>–<?php echo e(idlerpg_percent_label($rules['critical_max_percent'])); ?></td></tr>
                            <tr><td>Godsend</td><td><?php echo e(idlerpg_percent_label($rules['godsend_min_percent'])); ?>–<?php echo e(idlerpg_percent_label($rules['godsend_max_percent'])); ?> removed</td></tr>
                            <tr><td>Calamity</td><td><?php echo e(idlerpg_percent_label($rules['calamity_min_percent'])); ?>–<?php echo e(idlerpg_percent_label($rules['calamity_max_percent'])); ?> added</td></tr>
                            <tr><td>Alignment bonus</td><td><?php echo e(idlerpg_percent_label($rules['alignment_bonus_percent'])); ?></td></tr>
                            <tr><td>Team battle effect</td><td><?php echo e(idlerpg_percent_label($rules['team_battle_percent'])); ?></td></tr>
                        </tbody>
                    </table>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Items and unique artifacts</h3>
                    <table>
                        <tbody>
                            <tr><td>Level-up item chance</td><td><?php echo e(idlerpg_percent_label($rules['item_chance'])); ?></td></tr>
                            <tr><td>Unique items enabled</td><td><?php echo e(idlerpg_bool_label($rules['unique_items_enabled'])); ?></td></tr>
                            <tr><td>Unique item min level</td><td>lv.<?php echo e($rules['unique_item_min_level']); ?></td></tr>
                            <tr><td>Unique item chance</td><td><?php echo e(idlerpg_percent_label($rules['unique_item_chance'])); ?></td></tr>
                            <tr><td>Level reward badges start</td><td>lv.<?php echo e($rules['level_reward_min_level']); ?></td></tr>
                        </tbody>
                    </table>
                    <p class="muted">Unique artifacts can grant small bonuses. Fair item swaps trade one slot between players and do not destroy items.</p>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Quests and seasons</h3>
                    <table>
                        <tbody>
                            <tr><td>Quest min level</td><td>lv.<?php echo e($rules['quest_min_level']); ?></td></tr>
                            <tr><td>Quest min online time</td><td><?php echo e(idlerpg_seconds_label($rules['quest_min_online_seconds'])); ?></td></tr>
                            <tr><td>Quest types</td><td>time <?php echo e(idlerpg_bool_label($rules['quest_time_enabled'])); ?> / grid <?php echo e(idlerpg_bool_label($rules['quest_grid_enabled'])); ?></td></tr>
                            <tr><td>Quest type weights</td><td>time <?php echo e(idlerpg_weight_label($rules['quest_time_weight'])); ?> / grid <?php echo e(idlerpg_weight_label($rules['quest_grid_weight'])); ?></td></tr>
                            <tr><td>Quest interval</td><td><?php echo e(idlerpg_seconds_label($rules['quest_interval'])); ?></td></tr>
                            <tr><td>Time quest duration</td><td><?php echo e(idlerpg_seconds_label($rules['quest_time_min_duration'])); ?>–<?php echo e(idlerpg_seconds_label($rules['quest_time_max_duration'])); ?></td></tr>
                            <tr><td>Grid quest deadline</td><td><?php echo e(idlerpg_seconds_label($rules['quest_min_duration'])); ?>–<?php echo e(idlerpg_seconds_label($rules['quest_max_duration'])); ?></td></tr>
                            <tr><td>Quest reward</td><td><?php echo e(idlerpg_percent_label($rules['quest_reward_percent'])); ?> removed</td></tr>
                            <tr><td>Auto seasons</td><td><?php echo e(idlerpg_bool_label($rules['season_enabled'])); ?></td></tr>
                            <tr><td>Season length</td><td><?php echo e((int) $rules['season_duration_days']); ?> days</td></tr>
                            <tr><td>Reset on rollover</td><td><?php echo e(idlerpg_bool_label($rules['season_reset_on_rollover'])); ?></td></tr>
                            <tr><td>Hall of Fame size</td><td><?php echo e($rules['season_hof_size']); ?></td></tr>
                            <tr><td>Season achievement gates</td><td><?php echo e(idlerpg_bool_label($rules['season_achievement_gates_enabled'])); ?></td></tr>
                        </tbody>
                    </table>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Announcements and topic</h3>
                    <table>
                        <tbody>
                            <tr><td>Login announcements</td><td><?php echo e(idlerpg_bool_label($rules['announce_login'])); ?></td></tr>
                            <tr><td>Top announcement interval</td><td><?php echo e(idlerpg_seconds_label($rules['announce_top_interval'])); ?></td></tr>
                            <tr><td>Top announcement limit</td><td><?php echo e($rules['announce_top_limit']); ?></td></tr>
                            <tr><td>Room topic updates</td><td><?php echo e(idlerpg_bool_label($rules['update_room_topic'])); ?></td></tr>
                            <tr><td>Topic update interval</td><td><?php echo e(idlerpg_seconds_label($rules['topic_update_interval'])); ?></td></tr>
                            <tr><td>Topic custom text</td><td><?php echo trim((string) $rules['topic_custom_text']) !== '' ? e($rules['topic_custom_text']) : '<span class="muted">default</span>'; ?></td></tr>
                        </tbody>
                    </table>
                    <p class="muted">Topic output is rendered as: custom text followed by the current Top 3 players.</p>
                </article>

                <article class="idlerpg-rule-card">
                    <h3>Map and public export</h3>
                    <table>
                        <tbody>
                            <tr><td>Map size</td><td><?php echo e($rules['map_x']); ?> x <?php echo e($rules['map_y']); ?></td></tr>
                            <tr><td>Move step per second</td><td><?php echo e($rules['map_step_per_second']); ?></td></tr>
                            <tr><td>Grid battles</td><td><?php echo e(idlerpg_bool_label($rules['grid_battle_enabled'])); ?></td></tr>
                            <tr><td>Quest directed step</td><td>every <?php echo e(idlerpg_seconds_label($rules['quest_grid_step_seconds'])); ?></td></tr>
                            <tr><td>Event log limit</td><td><?php echo e($rules['event_log_limit']); ?></td></tr>
                            <tr><td>Event retention</td><td><?php echo e((int) $rules['event_retention_days']); ?> days</td></tr>
                            <tr><td>Exported events</td><td><?php echo e($rules['export_event_limit']); ?></td></tr>
                            <tr><td>Exported leaderboard</td><td><?php echo e($rules['export_top_limit']); ?></td></tr>
                        </tbody>
                    </table>
                </article>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($view === 'commands'): ?>
        <section class="idlerpg-commands">
            <h2>Commands</h2>
            <p class="section-text muted">
                The primary command is <code>,idlerpg</code>; <code>,irpg</code> and <code>,idle</code> are aliases.
                Most commands are room-scoped and should be used in the game room or in a MUC private message from that room.
                Duration arguments accept values such as <code>10m</code>, <code>1h30m</code> or <code>2d</code>.
            </p>

            <div class="idlerpg-command-section">
                <h3>Player commands</h3>
                <ul class="command-list">
                    <li><code>,idlerpg register &lt;character&gt; &lt;class&gt;</code> — create a character in the current room.</li>
                    <li><code>,idlerpg login</code> / <code>,idlerpg logout</code> — start or stop idling.</li>
                    <li><code>,idlerpg remove-me</code> / <code>,idlerpg removeme</code> — delete your own character from this room.</li>
                    <li><code>,idlerpg status [character]</code> / <code>,idlerpg me</code> / <code>,idlerpg whoami</code> — show character progress.</li>
                    <li><code>,idlerpg profile [character]</code> / <code>,idlerpg char [character]</code> / <code>,idlerpg character [character]</code> — show a detailed profile.</li>
                    <li><code>,idlerpg top [page|last|all]</code> — show the leaderboard.</li>
                    <li><code>,idlerpg players [page|last|all]</code> / <code>,idlerpg list [page|last|all]</code> — show known players and online state.</li>
                    <li><code>,idlerpg items [character]</code> — show normal and unique items.</li>
                    <li><code>,idlerpg achievements [character]</code> / <code>,idlerpg badges [character]</code> — show unlocked achievements.</li>
                    <li><code>,idlerpg achievements list [character]</code> / <code>,idlerpg achievements all [character]</code> / <code>,idlerpg achievements catalog [character]</code> — show the full achievement catalog.</li>
                    <li><code>,idlerpg title list</code> / <code>,idlerpg title show</code> — show unlocked titles.</li>
                    <li><code>,idlerpg title &lt;achievement|none&gt;</code> — choose or clear your public title. <code>clear</code> and <code>off</code> also clear it.</li>
                    <li><code>,idlerpg align &lt;good|neutral|evil&gt;</code> — set your character alignment.</li>
                    <li><code>,idlerpg duel &lt;character&gt;</code> / <code>,idlerpg challenge &lt;character&gt;</code> — challenge a nearby online character.</li>
                    <li><code>,idlerpg quest</code> — show the current quest or next quest check.</li>
                    <li><code>,idlerpg map</code> — show the ASCII map and public map export link when configured.</li>
                    <li><code>,idlerpg events [page|last|all]</code> / <code>,idlerpg eventlog [page|last|all]</code> / <code>,idlerpg news [page|last|all]</code> — show recent game events.</li>
                    <li><code>,idlerpg hof</code> / <code>,idlerpg hall</code> / <code>,idlerpg hall-of-fame</code> — show the Hall of Fame.</li>
                    <li><code>,idlerpg season</code> / <code>,idlerpg season status</code> — show current season state.</li>
                    <li><code>,idlerpg season hof</code> / <code>,idlerpg season hall</code> / <code>,idlerpg season hall-of-fame</code> — show Hall of Fame through the season command.</li>
                    <li><code>,idlerpg help</code> / <code>,idlerpg usage</code> — show command usage in chat.</li>
                </ul>
            </div>

            <div class="idlerpg-command-section idlerpg-admin-commands">
                <h3>Room owner/admin commands</h3>
                <p class="section-text muted">
                    These commands mutate room game state, public exports or room announcements and are limited to room owners/admins.
                </p>
                <ul class="command-list admin-list">
                    <li><code>,idlerpg on</code> / <code>,idlerpg off</code> / <code>,idlerpg enabled</code> — enable, disable or inspect IdleRPG for the current room.</li>
                    <li><code>,idlerpg stats</code> / <code>,idlerpg balance</code> — show room statistics and balance details.</li>
                    <li><code>,idlerpg push &lt;character&gt; &lt;duration&gt;</code> — remove time from a character's next-level clock.</li>
                    <li><code>,idlerpg setlevel &lt;character&gt; &lt;level&gt;</code> — set a character level and recalculate the next-level timer.</li>
                    <li><code>,idlerpg reset &lt;character&gt;</code> — reset level, timer, online time, items and penalties for a character.</li>
                    <li><code>,idlerpg delete &lt;character&gt;</code> / <code>,idlerpg remove &lt;character&gt;</code> — delete a character from the room.</li>
                    <li><code>,idlerpg announce top</code> — announce the current top players in the room.</li>
                    <li><code>,idlerpg topic update [custom text]</code> — update the room topic with optional custom text before the Top output.</li>
                    <li><code>,idlerpg export</code> — refresh the public IdleRPG export files for the room.</li>
                    <li><code>,idlerpg season end</code> / <code>,idlerpg season finish</code> — end the current season and start a new one without resetting players.</li>
                    <li><code>,idlerpg season reset</code> — end the current season, start a new one and reset players.</li>
                    <li><code>,idlerpg season extend [duration|manual]</code> — extend the current season, use the configured default, or make it manual/endless.</li>
                    <li><code>,idlerpg season clear-end</code> — remove the current season end timestamp and make the season manual/endless.</li>
                    <li><code>,idlerpg hof clear confirm</code> — clear the Hall of Fame for this room.</li>
                </ul>
            </div>
        </section>
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
            <li><a href="<?php echo e(idlerpg_view_url('items')); ?>">Unique Items</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('achievements')); ?>">Achievements</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('rules')); ?>">Rules</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('map')); ?>">World Map</a></li>
            <li><a href="<?php echo e(idlerpg_view_url('commands')); ?>">Commands</a></li>
            <?php if ($show_hof): ?>
                <li><a href="<?php echo e(idlerpg_view_url('hof')); ?>">Hall of Fame</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="box">
        <h2>Map legend</h2>
        <p class="muted">
            Blue = online, red = offline, orange = quest point.
            <code>[293,133] lv.16</code> means x=293, y=133 and level 16.
        </p>
    </div>

    <div class="box">
        <h2>Source</h2>
        <p class="muted">
            IdleRPG runs as a plugin for
            <a href="https://github.com/envs-net/envsbot">envsbot</a>.
            The source code is available on GitHub.
        </p>
    </div>

</aside>

<?php include '../neoenvs_footer.php'; ?>
