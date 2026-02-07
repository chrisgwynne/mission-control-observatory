<?php
/**
 * Observatory Template
 * Displays live agent activity feed from logs/activity.md
 */

// Parse activity log file - look in content/logs/ or fall back to workspace
$activityFile = $kirby->root('content') . '/logs/activity.md';

// Also check alternative location (workspace logs)
$workspaceLog = '/home/chris/.openclaw/workspace/mission-control/logs/activity.md';

$activities = [];
$source = '';

if (file_exists($activityFile)) {
    $content = file_get_contents($activityFile);
    $source = 'repo';
} elseif (file_exists($workspaceLog)) {
    $content = file_get_contents($workspaceLog);
    $source = 'workspace';
} else {
    $content = "# Mission Control Activity Log\n\nNo activity recorded yet.\n";
    $source = 'none';
}

// Split by entry separator
$entries = preg_split('/\n---\n/', $content);

foreach ($entries as $entry) {
    $entry = trim($entry);
    if (empty($entry) || strpos($entry, '# Mission Control Activity Log') !== false) {
        continue;
    }
    
    // Parse timestamp and agent info
    // Format: ## 2026-02-07 14:07:37 Minion completed task
    if (preg_match('/^##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(.+?)\s+(.+)$/m', $entry, $matches)) {
        $timestamp = $matches[1];
        $agent = $matches[2];
        $action = $matches[3];
        
        // Parse details
        $details = [];
        preg_match_all('/^- \*\*(.+?):\*\*\s*(.+)$/m', $entry, $detailMatches, PREG_SET_ORDER);
        foreach ($detailMatches as $dm) {
            $details[$dm[1]] = $dm[2];
        }
        
        $activities[] = [
            'timestamp' => $timestamp,
            'agent' => $agent,
            'action' => $action,
            'details' => $details,
            'raw' => $entry
        ];
    }
}

// Reverse to show newest first
$activities = array_reverse($activities);

// Agent color mapping
$agentColors = [
    'Minion' => '#3b82f6',
    'Scout' => '#22c55e',
    'Sage' => '#a855f7',
    'Quill' => '#f59e0b',
    'Xalt' => '#ec4899',
    'Observer' => '#64748b',
    'scout' => '#22c55e',
    'sage' => '#a855f7',
    'quill' => '#f59e0b',
    'xalt' => '#ec4899',
    'observer' => '#64748b'
];

// Format relative time
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return date('M j, H:i', $time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page->title() ?> | Mission Control Observatory</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
            background: #0a0a0f;
            color: #e2e8f0;
            line-height: 1.6;
            min-height: 100vh;
        }
        .site-header {
            background: linear-gradient(180deg, #0f172a 0%, #0a0a0f 100%);
            border-bottom: 1px solid #1e293b;
            padding: 1rem 2rem;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #e2e8f0;
        }
        .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .nav-links {
            margin-left: auto;
            display: flex;
            gap: 1.5rem;
        }
        .nav-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .nav-links a:hover { color: #e2e8f0; }
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #1e293b;
        }
        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #22c55e;
            text-transform: uppercase;
        }
        .live-indicator::before {
            content: '';
            width: 8px; height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.9); }
        }
        .activity-feed {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 8px;
        }
        .feed-header {
            background: #1e293b;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
        }
        .activity-list {
            max-height: 70vh;
            overflow-y: auto;
        }
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #1e293b;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1rem;
        }
        .activity-item:hover { background: #1e293b40; }
        .activity-time {
            font-size: 0.75rem;
            color: #64748b;
            text-align: right;
        }
        .time-relative { color: #475569; font-size: 0.7rem; }
        .agent-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #1e293b;
            border: 1px solid #334155;
        }
        .agent-badge::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .action-text { color: #cbd5e1; font-size: 0.9rem; }
        .activity-details {
            margin-left: 0.5rem;
            padding-left: 0.75rem;
            border-left: 2px solid #334155;
            font-size: 0.8rem;
        }
        .detail-row { display: flex; gap: 0.5rem; }
        .detail-label { color: #64748b; min-width: 80px; }
        .detail-value { color: #94a3b8; }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #64748b;
        }
        .site-footer {
            margin-top: 2rem;
            padding: 1.5rem;
            text-align: center;
            color: #64748b;
            font-size: 0.8rem;
            border-top: 1px solid #1e293b;
        }
        .activity-list::-webkit-scrollbar {
            width: 8px;
        }
        .activity-list::-webkit-scrollbar-track { background: #0f172a; }
        .activity-list::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <a href="/" class="logo">
                <div class="logo-icon">MC</div>
                <span class="logo-text">Mission Control</span>
            </a>
            <nav class="nav-links">
                <a href="/">Home</a>
                <a href="/agents">Agents</a>
                <a href="/observatory">Observatory</a>
                <a href="/about">About</a>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                Observatory
                <span class="live-indicator">Live</span>
            </h1>
            <p style="color:#94a3b8;margin-top:0.5rem;">
                Real-time activity feed from the Mission Control agent network.
                <?php if ($source === 'workspace'): ?>
                    <span style="color:#22c55e;">(Connected to live workspace)</span>
                <?php elseif ($source === 'none'): ?>
                    <span style="color:#ef4444;">(No activity log found)</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="activity-feed">
            <div class="feed-header">
                <span>Activity Log</span>
                <span>Source: <?= $source ?></span>
            </div>
            
            <div class="activity-list">
                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <div style="font-size:3rem;margin-bottom:1rem;">📡</div>
                        <p>No activity recorded yet.</p>
                        <p style="font-size:0.85rem;margin-top:0.5rem;">Run a task to see activity here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <?php 
                        $agentColor = $agentColors[$activity['agent']] ?? '#64748b';
                        $agentName = ucfirst($activity['agent']);
                        ?>
                        <div class="activity-item">
                            <div class="activity-time">
                                <div><?= date('H:i:s', strtotime($activity['timestamp'])) ?></div>
                                <div class="time-relative"><?= timeAgo($activity['timestamp']) ?></div>
                            </div>
                            
                            <div>
                                <div style="margin-bottom:0.5rem;">
                                    <span class="agent-badge" style="color:<?= $agentColor ?>;">
                                        <?= $agentName ?>
                                    </span>
                                    <span class="action-text"><?= $activity['action'] ?></span>
                                </div>
                                
                                <?php if (!empty($activity['details'])): ?>
                                    <div class="activity-details">
                                        <?php foreach ($activity['details'] as $key => $value): ?>
                                            <div class="detail-row">
                                                <span class="detail-label"><?= $key ?>:</span>
                                                <span class="detail-value"><?= htmlspecialchars($value) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer class="site-footer">
        Mission Control Observatory
    </footer>
    
    <script>
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>
