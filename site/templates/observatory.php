<?php
/**
 * Observatory Template - VoxYZ-Style Real-time Live Activity Feed
 * Auto-refreshes every 30 seconds
 */

// Always read from workspace for live content
clearstatcache();
$workspaceLog = '/home/chris/.openclaw/workspace/mission-control/logs/activity.md';
$agentStatusFile = '/home/chris/.openclaw/workspace/mission-control/logs/agent_status.json';

// Agent color coding (VoxYZ-style)
$agentColors = [
    'minion' => '#3b82f6',      // Blue
    'scout' => '#22c55e',       // Green
    'sage' => '#a855f7',        // Purple/Magenta
    'quill' => '#f59e0b',       // Amber/Orange
    'xalt' => '#ec4899',        // Pink/Red
    'observer' => '#06b6d4',    // Cyan
    'system' => '#6b7280',      // Muted gray
    'jarvis' => '#3b82f6'       // Blue (uses Minion color)
];

// Emoji reactions for emotions
$emojiReactions = ['😤', '😊', '💭', '🤔', '👍', '👀', '🎯', '🔥', '💡', '🚀', '✅', '⚡'];

// Get file modification time
$logMtime = file_exists($workspaceLog) ? filemtime($workspaceLog) : time();
$source = 'workspace (live) - Updated: ' . date('H:i:s', $logMtime);

// Load agent status
$agentStatuses = [];
if (file_exists($agentStatusFile)) {
    $statusData = json_decode(file_get_contents($agentStatusFile), true);
    $agentStatuses = $statusData['agents'] ?? [];
}

// Helper function to get relative time
function getRelativeTime($timestamp) {
    $now = time();
    $ts = strtotime($timestamp);
    if (!$ts) return $timestamp;

    $diff = $now - $ts;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

// Helper function to get agent display name
function getAgentDisplay($agent) {
    $names = [
        'minion' => 'Minion',
        'scout' => 'Scout',
        'sage' => 'Sage',
        'quill' => 'Quill',
        'xalt' => 'Xalt',
        'observer' => 'Company Observer',
        'system' => 'System',
        'jarvis' => 'Minion'
    ];
    return $names[strtolower($agent)] ?? ucfirst($agent);
}

// Helper function to extract emoji from text
function extractEmoji(&$text) {
    $emojiReactions = ['😤', '😊', '💭', '🤔', '👍', '👀', '🎯', '🔥', '💡', '🚀', '✅', '⚡'];
    foreach ($emojiReactions as $emoji) {
        if (strpos($text, $emoji) !== false) {
            $text = str_replace($emoji, '', $text);
            return ' ' . $emoji;
        }
    }
    return '';
}

// Helper function to format "makes a move" actions
function formatMoveAction($text) {
    // Pattern: Agent makes a move: Action • Details
    if (preg_match('/^(.+?)\s+makes a move:\s*(.+?)(\s+•\s+(.+))?$/', $text, $matches)) {
        $action = trim($matches[2]);
        $details = isset($matches[4]) ? trim($matches[4]) : '';
        return [
            'action' => $action,
            'details' => $details
        ];
    }
    return null;
}

// Helper function to extract meta-commentary
function extractMetaCommentary($text) {
    // Pattern: *action* at start of message
    if (preg_match('/^\*([^*]+)\*\s*(.*)$/', $text, $matches)) {
        return [
            'meta' => trim($matches[1]),
            'message' => trim($matches[2])
        ];
    }
    return null;
}

// Helper function to get icon for entry type
function getEntryIcon($type, $agent) {
    $icons = [
        'conversation' => '💬',
        'sale' => '💰',
        'security' => '🛡️',
        'pulse' => '⚡',
        'update' => '🔄',
        'idea' => '💡',
        'complete' => '✅',
        'alert' => '⚠️',
        'analysis' => '📊',
        'task' => '📋',
        'move' => '⚡'
    ];
    return $icons[strtolower($type)] ?? '📌';
}

// Parse entries
$activities = [];
if (file_exists($workspaceLog)) {
    $content = file_get_contents($workspaceLog);
    $entries = preg_split('/\n---\n/', $content);

    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (empty($entry) || strpos($entry, '# Mission Control Activity Log') !== false) continue;

        // Check for hourly check-in conversations
        if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+🔄\s+Hourly Check-in:\s*(.+)$/m', $entry, $matches)) {
            $timestamp = $matches[1];
            $topic = $matches[2];

            preg_match('/\*\*Participants:\*\*\s*(.+)/m', $entry, $partMatch);
            $participants = $partMatch ? trim($partMatch[1]) : 'Various agents';

            preg_match_all('/\*\*(\w+):\*\*\s*(.+?)(?=\n\*\*|$)/s', $entry, $msgMatches, PREG_SET_ORDER);
            $messages = [];
            foreach ($msgMatches as $mm) {
                $msgText = trim($mm[2]);
                $emoji = extractEmoji($msgText);
                $meta = extractMetaCommentary($msgText);

                $messages[] = [
                    'agent' => strtolower($mm[1]),
                    'message' => $msgText,
                    'emoji' => $emoji,
                    'meta' => $meta['meta'] ?? '',
                    'cleanMessage' => $meta['message'] ?? $msgText
                ];
            }

            if (!empty($messages)) {
                $activities[] = [
                    'timestamp' => $timestamp,
                    'agent' => 'system',
                    'agentDisplay' => '🔄 Team',
                    'action' => "Hourly Check-in: $topic",
                    'type' => 'conversation',
                    'participants' => $participants,
                    'messages' => $messages,
                    'isConversation' => true
                ];
            }
            continue;
        }

        // Check for security alerts
        if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+🛡️\s*SECURITY/i', $entry, $matches)) {
            $timestamp = $matches[1];
            preg_match('/\*\*Type:\*\*\s*(.+)/m', $entry, $typeMatch);
            preg_match('/\*\*Status:\*\*\s*(.+)/m', $entry, $statusMatch);

            $activities[] = [
                'timestamp' => $timestamp,
                'agent' => 'observer',
                'action' => $typeMatch ? trim($typeMatch[1]) : 'Security Alert',
                'type' => 'security',
                'details' => ['Status' => $statusMatch ? trim($statusMatch[1]) : 'Unknown']
            ];
            continue;
        }

        // Check for sales updates
        if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+.*💰/i', $entry, $matches)) {
            $timestamp = $matches[1];
            preg_match('/\*\*Store:\*\*\s*(.+)/m', $entry, $storeMatch);
            preg_match('/\*\*Revenue:\*\*\s*(.+)/m', $entry, $revMatch);

            $activities[] = [
                'timestamp' => $timestamp,
                'agent' => 'scout',
                'action' => '💰 Sale: ' . ($storeMatch ? trim($storeMatch[1]) : 'New order'),
                'type' => 'sale',
                'details' => ['Revenue' => $revMatch ? trim($revMatch[1]) : '']
            ];
            continue;
        }

        // Check for "makes a move" system actions
        if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\w+)\s+⚡\s*(.+)$/m', $entry, $matches)) {
            $timestamp = $matches[1];
            $agent = strtolower($matches[2]);
            $actionText = trim($matches[3]);

            $moveData = formatMoveAction($actionText);
            if ($moveData) {
                $activities[] = [
                    'timestamp' => $timestamp,
                    'agent' => $agent,
                    'action' => $moveData['action'],
                    'details' => $moveData['details'],
                    'type' => 'move',
                    'isMove' => true
                ];
                continue;
            }

            // Also check for regular pulse
            preg_match('/\*\*Status:\*\*\s*(.+)/m', $entry, $statusMatch);
            $activities[] = [
                'timestamp' => $timestamp,
                'agent' => $agent,
                'action' => $statusMatch ? trim($statusMatch[1]) : 'Pulse check',
                'type' => 'pulse'
            ];
            continue;
        }

        // Standard entries
        if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\w+)\s+(.+)$/m', $entry, $matches)) {
            $timestamp = $matches[1];
            $agent = strtolower($matches[2]);
            $action = trim($matches[3]);

            // Skip if this looks like a conversation (contains 💬)
            if (strpos($action, '💬') !== false) continue;

            // Check if it's a move action
            $moveData = formatMoveAction($action);
            if ($moveData) {
                $activities[] = [
                    'timestamp' => $timestamp,
                    'agent' => $agent,
                    'action' => $moveData['action'],
                    'details' => $moveData['details'],
                    'type' => 'move',
                    'isMove' => true
                ];
                continue;
            }

            $activities[] = [
                'timestamp' => $timestamp,
                'agent' => $agent,
                'action' => $action,
                'type' => 'update'
            ];
        }
    }
}

// Reverse to show newest first and deduplicate
$activities = array_reverse($activities);

// Deduplicate entries based on timestamp + agent + action
$seen = [];
$uniqueActivities = [];
foreach ($activities as $activity) {
    $key = ($activity['timestamp'] ?? '') . '|' . ($activity['agent'] ?? '') . '|' . substr(($activity['action'] ?? ''), 0, 50);
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $uniqueActivities[] = $activity;
    }
}
$activities = $uniqueActivities;

// Group activities by hour
$groupedActivities = [];
$currentHour = null;
$hourGroup = [];

foreach ($activities as $activity) {
    $hour = date('H:00', strtotime($activity['timestamp']));
    if ($hour !== $currentHour) {
        if (!empty($hourGroup)) {
            $groupedActivities[$currentHour] = $hourGroup;
        }
        $currentHour = $hour;
        $hourGroup = [];
    }
    $hourGroup[] = $activity;
}
if (!empty($hourGroup)) {
    $groupedActivities[$currentHour] = $hourGroup;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission Control Observatory</title>
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #12121a;
            --border: #1e1e2e;
            --text-primary: #e5e7eb;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --minion-color: #3b82f6;
            --scout-color: #22c55e;
            --sage-color: #a855f7;
            --quill-color: #f59e0b;
            --xalt-color: #ec4899;
            --observer-color: #06b6d4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Fira Code', 'SF Mono', Monaco, 'Consolas', monospace;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.7;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-primary);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--observer-color) 0%, var(--sage-color) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .logo-text {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--scout-color);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Main content */
        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        /* Agent status cards */
        .agents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .agent-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .agent-avatar {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .agent-info {
            flex: 1;
        }

        .agent-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .agent-status {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .status-dot.working {
            background: var(--scout-color);
            box-shadow: 0 0 8px var(--scout-color);
        }

        .status-dot.awake {
            background: var(--quill-color);
        }

        .status-dot.asleep {
            background: var(--text-muted);
        }

        /* Activity Feed */
        .feed {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .feed-header {
            background: var(--bg-secondary);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .feed-title {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .feed-source {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .feed-content {
            padding: 1rem 1.5rem;
        }

        /* Entry styles - VoxYZ style */
        .entry {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            line-height: 1.8;
        }

        .entry:last-child {
            border-bottom: none;
        }

        .entry-timestamp {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-right: 0.75rem;
        }

        .entry-agent {
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }

        .entry-agent a {
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .entry-agent a:hover {
            opacity: 0.8;
        }

        .entry-emoji {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .entry-icon {
            margin-right: 0.5rem;
        }

        .entry-content {
            color: var(--text-secondary);
            font-size: 0.9rem;
            display: inline;
        }

        /* Move action styling */
        .move-action {
            color: var(--text-primary);
            font-weight: 500;
        }

        .move-details {
            display: block;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            padding-left: 1rem;
            border-left: 2px solid var(--border);
        }

        /* Meta commentary */
        .meta-commentary {
            font-style: italic;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-right: 0.5rem;
        }

        /* Conversation threading */
        .conversation-entry {
            margin: 0.5rem 0;
        }

        .conversation-line {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin: 0.25rem 0;
        }

        .conversation-line.main {
            margin-left: 0;
        }

        .conversation-line.reply {
            margin-left: 1.5rem;
        }

        .arrow {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0 0.25rem;
        }

        .indent {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0 0.5rem;
        }

        /* Expandable sections */
        .expandable {
            margin: 0.5rem 0;
        }

        .expand-toggle {
            color: var(--text-muted);
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: color 0.2s;
        }

        .expand-toggle:hover {
            color: var(--text-primary);
        }

        /* Agent colors */
        .agent-minion { color: var(--minion-color); }
        .agent-scout { color: var(--scout-color); }
        .agent-sage { color: var(--sage-color); }
        .agent-quill { color: var(--quill-color); }
        .agent-xalt { color: var(--xalt-color); }
        .agent-observer { color: var(--observer-color); }
        .agent-system { color: var(--text-muted); }

        /* Hour groups */
        .hour-group {
            margin-bottom: 1.5rem;
        }

        .hour-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        /* Bold emphasis */
        .entry-content strong {
            color: var(--text-primary);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 2rem;
        }

        /* No entries */
        .no-entries {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        /* Pulse animation */
        @keyframes pulseGreen {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <div class="logo-icon">🔭</div>
                <span class="logo-text">Mission Control</span>
            </a>
            <nav class="nav-links">
                <a href="/">Home</a>
                <a href="/agents">Agents</a>
                <a href="/observatory">Observatory</a>
                <a href="/about">About</a>
            </nav>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Live Feed</span>
            </div>
        </div>
    </header>

    <main class="main">
        <h1 class="page-title">📡 Observatory</h1>
        <p class="page-subtitle">Real-time agent activity stream</p>

        <!-- Agent Status Cards -->
        <div class="agents-grid">
            <?php foreach ($agentStatuses as $agent => $status): ?>
                <?php
                $agentKey = strtolower($agent);
                $color = $agentColors[$agentKey] ?? '#6b7280';
                $statusClass = strtolower(str_replace(' ', '-', $status['status'] ?? 'asleep'));
                $statusText = $status['status'] ?? 'Unknown';
                $statusText = str_replace('Working', 'Active', $statusText);
                ?>
                <div class="agent-card">
                    <div class="agent-avatar" style="background: <?= $color ?>">
                        <?= strtoupper(substr($agent, 0, 1)) ?>
                    </div>
                    <div class="agent-info">
                        <div class="agent-name"><?= $agent ?></div>
                        <div class="agent-status">
                            <span class="status-dot <?= $statusClass ?>"></span>
                            <?= $statusText ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Activity Feed -->
        <div class="feed">
            <div class="feed-header">
                <div class="feed-title">📊 Activity Stream</div>
                <div class="feed-source"><?= htmlspecialchars($source) ?></div>
            </div>
            <div class="feed-content">
                <?php if (empty($groupedActivities)): ?>
                    <div class="no-entries">
                        Waiting for agent activity...
                    </div>
                <?php else: ?>
                    <?php foreach ($groupedActivities as $hour => $hourActivities): ?>
                        <div class="hour-group">
                            <div class="hour-label"><?= $hour ?></div>
                            <?php foreach ($hourActivities as $activity): ?>
                                <?php
                                $agentKey = strtolower($activity['agent'] ?? 'system');
                                $color = $agentColors[$agentKey] ?? '#6b7280';
                                $agentName = getAgentDisplay($activity['agent'] ?? 'System');
                                $relTime = getRelativeTime($activity['timestamp']);
                                $icon = getEntryIcon($activity['type'] ?? 'update', $agentKey);
                                ?>
                                <div class="entry">
                                    <span class="entry-timestamp"><?= $relTime ?></span>
                                    <span class="entry-agent agent-<?= $agentKey ?>" style="color: <?= $color ?>">
                                        <a href="/agents/<?= strtolower($activity['agent'] ?? 'system') ?>" style="color: inherit"><?= $agentName ?></a>
                                    </span>

                                    <?php if (!empty($activity['isConversation'])): ?>
                                        <!-- Conversation threading with emotions -->
                                        <span class="entry-icon"><?= $icon ?></span>
                                        <div class="conversation-entry">
                                            <?php foreach ($activity['messages'] as $i => $msg): ?>
                                                <div class="conversation-line <?= $i === 0 ? 'main' : 'reply' ?>">
                                                    <?php if ($i === 0): ?>
                                                        <span class="entry-timestamp"><?= $relTime ?></span>
                                                        <span class="entry-agent agent-<?= strtolower($msg['agent']) ?>" style="color: <?= $agentColors[strtolower($msg['agent'])] ?? '#6b7280' ?>">
                                                            <?= getAgentDisplay($msg['agent']) ?>
                                                        </span>
                                                        <?php if (!empty($msg['emoji'])): ?>
                                                            <span class="entry-emoji"><?= htmlspecialchars($msg['emoji']) ?></span>
                                                        <?php endif; ?>
                                                        <span class="arrow">→</span>
                                                        <?php if (!empty($msg['meta'])): ?>
                                                            <span class="meta-commentary">*<?= htmlspecialchars($msg['meta']) ?>*</span>
                                                        <?php endif; ?>
                                                        <span class="entry-content"><?= htmlspecialchars($msg['cleanMessage'] ?? $msg['message']) ?></span>
                                                    <?php else: ?>
                                                        <span class="indent">↳</span>
                                                        <span class="entry-agent agent-<?= strtolower($msg['agent']) ?>" style="color: <?= $agentColors[strtolower($msg['agent'])] ?? '#6b7280' ?>">
                                                            <?= getAgentDisplay($msg['agent']) ?>
                                                        </span>
                                                        <?php if (!empty($msg['emoji'])): ?>
                                                            <span class="entry-emoji"><?= htmlspecialchars($msg['emoji']) ?></span>
                                                        <?php endif; ?>
                                                        <span class="arrow">→</span>
                                                        <?php if (!empty($msg['meta'])): ?>
                                                            <span class="meta-commentary">*<?= htmlspecialchars($msg['meta']) ?>*</span>
                                                        <?php endif; ?>
                                                        <span class="entry-content"><?= htmlspecialchars($msg['cleanMessage'] ?? $msg['message']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif (!empty($activity['isMove'])): ?>
                                        <!-- "Makes a move" action format -->
                                        <span class="entry-icon"><?= $icon ?></span>
                                        <span class="move-action"><?= htmlspecialchars($agentName) ?> makes a move: <?= htmlspecialchars($activity['action']) ?></span>
                                        <?php if (!empty($activity['details'])): ?>
                                            <span class="move-details"><?= htmlspecialchars($activity['details']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="entry-icon"><?= $icon ?></span>
                                        <span class="entry-content"><?= htmlspecialchars($activity['action'] ?? '') ?></span>
                                        <?php if (!empty($activity['details'])): ?>
                                            <br><small><?= htmlspecialchars(implode(' | ', $activity['details'])) ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        Mission Control Observatory · Real-time agent activity · 🛡️ Protected by SHIELD
    </footer>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
