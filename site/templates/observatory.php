<?php
/**
 * Internal Ops Intelligence Feed - Dark Console UI
 * Real-time operational telemetry
 */

clearstatcache();
$workspaceLog = '/home/chris/.openclaw/workspace/mission-control/logs/activity.md';
$agentStatusFile = '/home/chris/.openclaw/workspace/mission-control/logs/agent_status.json';

// Actor colors (serious, muted tones)
$actorColors = [
    'minion' => '#3b82f6',      // Blue
    'scout' => '#22c55e',       // Green
    'sage' => '#a855f7',        // Purple
    'quill' => '#f59e0b',       // Amber
    'xalt' => '#ef4444',        // Red
    'observer' => '#06b6d4',    // Cyan
    'system' => '#6b7280',       // Gray
];

$logMtime = file_exists($workspaceLog) ? filemtime($workspaceLog) : time();
$source = 'STREAM: ' . date('H:i:s', $logMtime);

// Load agent status
$agentStatuses = [];
if (file_exists($agentStatusFile)) {
    $statusData = json_decode(file_get_contents($agentStatusFile), true);
    $agentStatuses = $statusData['agents'] ?? [];
}

// Helper functions
function getRelativeTime($timestamp) {
    $now = time();
    $ts = strtotime($timestamp);
    if (!$ts) return '--:--';
    $diff = $now - $ts;
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

function getActorDisplay($actor) {
    $names = [
        'minion' => 'Minion',
        'scout' => 'Scout',
        'sage' => 'Sage',
        'quill' => 'Quill',
        'xalt' => 'Xalt',
        'observer' => 'Observer',
        'system' => 'System',
        'jarvis' => 'Minion'
    ];
    return $names[strtolower($actor)] ?? ucfirst($actor);
}

function detectEntryType($action, $details) {
    $actionLower = strtolower($action);
    $detailsLower = strtolower($details);

    if (strpos($actionLower, 'pulse') !== false || strpos($detailsLower, 'holding pattern') !== false) {
        return 'pulse';
    }
    if (strpos($actionLower, 'makes a move') !== false || strpos($actionLower, 'proposal') !== false || strpos($actionLower, 'approved') !== false) {
        return 'action';
    }
    if (strpos($actionLower, 'debate') !== false || strpos($actionLower, 'what defines') !== false || strpos($actionLower, 'criteria') !== false) {
        return 'commentary';
    }
    if (strpos($actionLower, 'decision') !== false || strpos($actionLower, 'quota') !== false || strpos($actionLower, 'threshold') !== false) {
        return 'decision';
    }
    return 'pulse';
}

// Parse entries
$feed = [];
if (file_exists($workspaceLog)) {
    $content = file_get_contents($workspaceLog);
    $entries = preg_split('/\n---\n/', $content);

    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (empty($entry) || strpos($entry, '# Mission Control Activity Log') !== false) continue;

        // Extract timestamp and actor
        if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\w+)\s*(.+)$/m', $entry, $matches)) {
            $timestamp = $matches[1];
            $actor = strtolower($matches[2]);
            $action = trim($matches[3]);

            // Extract details
            $details = '';
            if (preg_match('/•\s*(.+?)(\n|$)/', $action, $detailMatch)) {
                $details = trim($detailMatch[1]);
                $action = trim(str_replace($detailMatch[0], '', $action));
            }

            // Remove emoji prefix from action
            $action = trim(preg_replace('/^[💬⚡🛡️💰📊🔄✅🔍]+\s*/', '', $action));

            $type = detectEntryType($action, $details);

            $feed[] = [
                'timestamp' => $timestamp,
                'actor' => $actor,
                'action' => $action,
                'details' => $details,
                'type' => $type
            ];
        }
    }
}

$feed = array_reverse($feed);
$feedCount = count($feed);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPS INTELLIGENCE // STREAM</title>
    <style>
        :root {
            --bg-primary: #0a0f0a;
            --bg-secondary: #0d140d;
            --bg-tertiary: #111811;
            --border: #1a2a1a;
            --text-primary: #c0d0c0;
            --text-dim: #6a7a6a;
            --text-muted: #3a4a3a;
            --accent-green: #22c55e;
            --accent-amber: #f59e0b;
            --accent-red: #ef4444;
            --accent-blue: #3b82f6;
            --accent-purple: #a855f7;
            --accent-cyan: #06b6d4;
            --font-mono: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-mono);
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 13px;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Scanline effect */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 0, 0, 0.03) 0px,
                rgba(0, 0, 0, 0.03) 1px,
                transparent 1px,
                transparent 2px
            );
            pointer-events: none;
            z-index: 1000;
        }

        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }

        /* Header */
        .header {
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .title-block h1 {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 0.15em;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .title-block .subtitle {
            font-size: 11px;
            color: var(--text-dim);
            letter-spacing: 0.1em;
        }

        .status-block {
            text-align: right;
            font-size: 11px;
            color: var(--text-dim);
        }

        .status-block .stream-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: blink 2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .metrics-row {
            display: flex;
            gap: 2rem;
            font-size: 11px;
            color: var(--text-muted);
        }

        .metric {
            display: flex;
            gap: 0.5rem;
        }

        .metric-label { color: var(--text-dim); }
        .metric-value { color: var(--text-primary); }

        /* Actor Legend */
        .actor-legend {
            display: flex;
            gap: 1.5rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .actor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 11px;
        }

        .actor-indicator {
            width: 4px;
            height: 4px;
            border-radius: 50%;
        }

        /* Feed */
        .feed {
            position: relative;
        }

        .feed-entry {
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--border);
            display: grid;
            grid-template-columns: 70px 100px 1fr;
            gap: 1rem;
            align-items: flex-start;
            transition: background 0.2s;
        }

        .feed-entry:hover { background: rgba(34, 197, 94, 0.02); }

        .entry-timestamp {
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }

        .entry-actor {
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .entry-content {
            font-size: 13px;
            color: var(--text-primary);
        }

        .entry-details {
            font-size: 11px;
            color: var(--text-dim);
            margin-top: 0.25rem;
            padding-left: 1rem;
            border-left: 2px solid var(--border);
        }

        /* Entry types */
        .entry-type-pulse .entry-content {
            color: var(--text-dim);
            font-style: italic;
        }

        .entry-type-commentary .entry-actor {
            font-style: normal;
        }

        .entry-type-action .entry-content {
            color: var(--text-primary);
            font-weight: 500;
        }

        .entry-type-action .entry-content::before {
            content: '⚡ ';
        }

        .entry-type-decision .entry-content {
            color: var(--accent-green);
            font-weight: 500;
        }

        .entry-type-decision .entry-details {
            color: var(--text-dim);
        }

        /* Actor colors */
        .actor-minion { color: var(--accent-blue); }
        .actor-scout { color: var(--accent-green); }
        .actor-sage { color: var(--accent-purple); }
        .actor-quill { color: var(--accent-amber); }
        .actor-xalt { color: var(--accent-red); }
        .actor-observer { color: var(--accent-cyan); }
        .actor-system { color: var(--text-muted); }

        /* Footer */
        .footer {
            margin-top: 3rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: var(--text-muted);
        }

        .no-entries {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
            font-style: italic;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .feed-entry {
                grid-template-columns: 1fr;
                gap: 0.25rem;
            }
            .entry-timestamp { color: var(--text-dim); }
            .actor-legend { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-top">
                <div class="title-block">
                    <h1>OPS INTELLIGENCE</h1>
                    <div class="subtitle">// INTERNAL STREAM</div>
                </div>
                <div class="status-block">
                    <div class="stream-status">
                        <div class="status-dot"></div>
                        <span>LIVE</span>
                    </div>
                    <div class="metrics-row">
                        <div class="metric">
                            <span class="metric-label">ENTRIES:</span>
                            <span class="metric-value"><?= $feedCount ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">SOURCE:</span>
                            <span class="metric-value">LOG</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actor Legend -->
            <div class="actor-legend">
                <?php foreach ($actorColors as $actor => $color): ?>
                    <?php if ($actor !== 'system'): ?>
                        <div class="actor">
                            <div class="actor-indicator" style="background: <?= $color ?>"></div>
                            <span style="color: <?= $color ?>"><?= getActorDisplay($actor) ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </header>

        <!-- Feed Stream -->
        <main class="feed">
            <?php if (empty($feed)): ?>
                <div class="no-entries">
                    // WAITING FOR STREAM DATA...<br>
                    // NO ENTRIES DETECTED
                </div>
            <?php else: ?>
                <?php foreach ($feed as $item): ?>
                    <div class="feed-entry entry-type-<?= htmlspecialchars($item['type']) ?>">
                        <div class="entry-timestamp">
                            <?= getRelativeTime($item['timestamp']) ?>
                        </div>
                        <div class="entry-actor actor-<?= htmlspecialchars($item['actor']) ?>">
                            <?= getActorDisplay($item['actor']) ?>
                        </div>
                        <div class="entry-content">
                            <?= htmlspecialchars($item['action']) ?>
                            <?php if (!empty($item['details'])): ?>
                                <div class="entry-details">
                                    <?= htmlspecialchars($item['details']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div>OPS INTELLIGENCE FEED // VER 1.0</div>
            <div>READ-ONLY STREAM</div>
        </footer>
    </div>

    <script>
        // Auto-refresh every 45 seconds
        setInterval(() => { location.reload(); }, 45000);
    </script>
</body>
</html>
