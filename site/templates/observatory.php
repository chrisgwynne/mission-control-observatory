<?php
/**
 * Observatory Template - Real-time Live Activity Feed
 * Inspired by https://www.voxyz.space/stage
 */

// Parse activity log file
$activityFile = $kirby->root('content') . '/logs/activity.md';
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

// Parse entries
$entries = preg_split('/\n---\n/', $content);
foreach ($entries as $entry) {
    $entry = trim($entry);
    if (empty($entry) || strpos($entry, '# Mission Control Activity Log') !== false) continue;
    
    if (preg_match('/^##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(.+?)\s+(.+)$/m', $entry, $matches)) {
        $timestamp = $matches[1];
        $agent = $matches[2];
        $action = $matches[3];
        
        $details = [];
        preg_match_all('/^- \*\*(.+?):\*\*\s*(.+)$/m', $entry, $detailMatches, PREG_SET_ORDER);
        foreach ($detailMatches as $dm) {
            $details[$dm[1]] = $dm[2];
        }
        
        // Determine activity type
        $type = 'system';
        if (stripos($action, 'search') !== false) $type = 'search';
        elseif (stripos($action, 'received') !== false) $type = 'system';
        elseif (stripos($action, 'delegated') !== false) $type = 'system';
        elseif (stripos($action, 'completed') !== false) $type = 'complete';
        elseif (stripos($action, 'filtered') !== false) $type = 'search';
        elseif (stripos($action, 'cache') !== false) $type = 'system';
        
        $activities[] = [
            'timestamp' => $timestamp,
            'agent' => strtolower($agent),
            'agentDisplay' => ucfirst($agent),
            'action' => $action,
            'type' => $type,
            'details' => $details
        ];
    }
}

$activities = array_reverse($activities);

// Agent config
$agents = [
    'minion' => ['name' => 'Minion', 'role' => 'Chief of Staff', 'color' => '#3b82f6', 'icon' => '👑'],
    'scout' => ['name' => 'Scout', 'role' => 'Head of Growth', 'color' => '#22c55e', 'icon' => '🔭'],
    'sage' => ['name' => 'Sage', 'role' => 'Head of Research', 'color' => '#a855f7', 'icon' => '📚'],
    'quill' => ['name' => 'Quill', 'role' => 'Creative Director', 'color' => '#f59e0b', 'icon' => '✍️'],
    'xalt' => ['name' => 'Xalt', 'role' => 'Social Media Director', 'color' => '#ec4899', 'icon' => '📱'],
    'observer' => ['name' => 'Observer', 'role' => 'Operations Analyst', 'color' => '#64748b', 'icon' => '👁️']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observatory | Mission Control</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #0f172a;
            --bg-tertiary: #1e293b;
            --border: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #e2e8f0;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --accent-blue: #3b82f6;
            --accent-green: #22c55e;
            --accent-purple: #a855f7;
            --accent-amber: #f59e0b;
            --accent-pink: #ec4899;
            --accent-slate: #64748b;
        }
        
        body {
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
            background: var(--bg-primary);
            color: var(--text-secondary);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Scanline effect */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                rgba(0,0,0,0.15) 0px,
                rgba(0,0,0,0.15) 1px,
                transparent 1px,
                transparent 2px
            );
            pointer-events: none;
            z-index: 1000;
        }
        
        /* Header */
        .site-header {
            background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        .header-content {
            max-width: 1400px;
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
            color: var(--text-primary);
        }
        
        .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }
        
        .nav-links {
            margin-left: auto;
            display: flex;
            gap: 2rem;
        }
        
        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--text-primary);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px; left: 0;
            width: 0; height: 2px;
            background: var(--accent-blue);
            transition: width 0.2s;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* Main Layout */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--accent-green);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .live-badge::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
            box-shadow: 0 0 10px var(--accent-green);
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }
        
        .connection-status {
            font-size: 0.8rem;
            color: var(--text-dim);
        }
        
        .connection-status.connected {
            color: var(--accent-green);
        }
        
        /* Agent Status Cards */
        .agent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .agent-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .agent-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--agent-color, var(--accent-blue));
            opacity: 0.5;
        }
        
        .agent-card.active {
            border-color: var(--agent-color, var(--accent-blue));
            box-shadow: 0 0 30px rgba(0,0,0,0.3), 0 0 0 1px var(--agent-color, var(--accent-blue));
        }
        
        .agent-card.active::before {
            opacity: 1;
            box-shadow: 0 0 20px var(--agent-color, var(--accent-blue));
        }
        
        .agent-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .agent-avatar {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: var(--agent-color, var(--accent-blue));
            opacity: 0.2;
        }
        
        .agent-info h3 {
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 600;
        }
        
        .agent-info .role {
            font-size: 0.75rem;
            color: var(--text-dim);
        }
        
        .agent-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            font-size: 0.8rem;
        }
        
        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--text-dim);
        }
        
        .status-dot.working {
            background: var(--accent-green);
            animation: pulse 1s infinite;
        }
        
        .status-dot.idle {
            background: var(--text-dim);
        }
        
        .status-text {
            color: var(--text-muted);
        }
        
        .status-text.working {
            color: var(--accent-green);
        }
        
        .last-action {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--text-dim);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Activity Feed */
        .feed-container {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .feed-header {
            background: var(--bg-tertiary);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }
        
        .feed-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .feed-controls {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        
        .control-btn {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .control-btn:hover {
            border-color: var(--text-muted);
            color: var(--text-secondary);
        }
        
        .control-btn.active {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }
        
        .activity-list {
            max-height: 60vh;
            overflow-y: auto;
            padding: 0.5rem 0;
        }
        
        .activity-group {
            border-bottom: 1px solid var(--border);
        }
        
        .activity-group:last-child {
            border-bottom: none;
        }
        
        .activity-item {
            display: flex;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .activity-item:hover {
            background: rgba(255,255,255,0.02);
        }
        
        .activity-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .activity-icon.system { background: rgba(59, 130, 246, 0.15); }
        .activity-icon.search { background: rgba(34, 197, 94, 0.15); }
        .activity-icon.complete { background: rgba(168, 85, 247, 0.15); }
        .activity-icon.message { background: rgba(245, 158, 11, 0.15); }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        .activity-agent {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--agent-color, var(--text-primary));
        }
        
        .activity-action {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .activity-time {
            margin-left: auto;
            font-size: 0.75rem;
            color: var(--text-dim);
        }
        
        .activity-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.5rem;
            margin-top: 0.5rem;
            font-size: 0.75rem;
        }
        
        .detail-item {
            display: flex;
            gap: 0.375rem;
        }
        
        .detail-label {
            color: var(--text-dim);
        }
        
        .detail-value {
            color: var(--text-muted);
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Expandable sections */
        .expand-toggle {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            color: var(--text-dim);
            font-family: inherit;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .expand-toggle:hover {
            color: var(--text-secondary);
            background: rgba(255,255,255,0.02);
        }
        
        .expand-toggle .arrow {
            transition: transform 0.2s;
        }
        
        .expand-toggle.expanded .arrow {
            transform: rotate(180deg);
        }
        
        .collapsed-items {
            display: none;
        }
        
        .collapsed-items.expanded {
            display: block;
        }
        
        /* New entry highlight */
        .activity-item.new {
            background: rgba(34, 197, 94, 0.05);
            border-left: 3px solid var(--accent-green);
        }
        
        /* Scrollbar */
        .activity-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .activity-list::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        
        .activity-list::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }
        
        .activity-list::-webkit-scrollbar-thumb:hover {
            background: var(--text-dim);
        }
        
        /* Footer */
        .site-footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-dim);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 2rem;
        }
        
        /* Loading state */
        .loading-skeleton {
            padding: 1rem 1.5rem;
            display: flex;
            gap: 1rem;
            opacity: 0.5;
        }
        
        .skeleton-icon {
            width: 32px; height: 32px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            animation: shimmer 1.5s infinite;
        }
        
        .skeleton-content {
            flex: 1;
        }
        
        .skeleton-line {
            height: 12px;
            background: var(--bg-tertiary);
            border-radius: 4px;
            margin-bottom: 8px;
            animation: shimmer 1.5s infinite;
        }
        
        @keyframes shimmer {
            0% { opacity: 0.4; }
            50% { opacity: 0.8; }
            100% { opacity: 0.4; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <a href="/" class="logo">
                <div class="logo-icon">MC</div>
                <span>Mission Control</span>
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
                <span class="live-badge">Live</span>
            </h1>
            <span id="connectionStatus" class="connection-status">Connecting...</span>
        </div>

        <!-- Agent Status Cards -->
        <div class="agent-grid" id="agentGrid">
            <?php foreach ($agents as $key => $agent): ?
            <div class="agent-card" data-agent="<?= $key ?>" style="--agent-color: <?= $agent['color'] ?>">
                <div class="agent-header">
                    <div class="agent-avatar"><?= $agent['icon'] ?></div>
                    <div class="agent-info">
                        <h3><?= $agent['name'] ?></h3>
                        <span class="role"><?= $agent['role'] ?></span>
                    </div>
                </div>
                <div class="agent-status">
                    <span class="status-dot" id="status-dot-<?= $key ?>"></span>
                    <span class="status-text" id="status-text-<?= $key ?>">Idle</span>
                </div>
                <div class="last-action" id="last-action-<?= $key ?>">Waiting for activity...</div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Activity Feed -->
        <div class="feed-container">
            <div class="feed-header">
                <span class="feed-title">Activity Log</span>
                <div class="feed-controls">
                    <button class="control-btn" id="autoScrollBtn" onclick="toggleAutoScroll()">
                        ⬇ Auto-scroll
                    </button>
                    <button class="control-btn" onclick="manualRefresh()">
                        ↻ Refresh
                    </button>
                </div>
            </div>
            
            <div class="activity-list" id="activityList">
                <!-- Initial content from PHP -->
                <?php 
                $grouped = [];
                foreach ($activities as $activity) {
                    $grouped[$activity['agent']][] = $activity;
                }
                
                foreach ($grouped as $agent => $items): 
                    $agentData = $agents[$agent] ?? ['color' => '#64748b'];
                    $recent = array_slice($items, 0, 3);
                    $older = array_slice($items, 3);
                ?>
                    <?php foreach ($recent as $item): ?>
                    <div class="activity-item" data-timestamp="<?= $item['timestamp'] ?>" data-agent="<?= $agent ?>">
                        <div class="activity-icon <?= $item['type'] ?>" style="color: <?= $agentData['color'] ?>">
                            <?php
                            $icons = ['system' => '⚡', 'search' => '🔍', 'complete' => '✅', 'message' => '💬'];
                            echo $icons[$item['type']] ?? '⚡';
                            ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-meta">
                                <span class="activity-agent" style="color: <?= $agentData['color'] ?>">
                                    <?= $item['agentDisplay'] ?>
                                </span>
                                <span class="activity-action"><?= $item['action'] ?></span>
                                <span class="activity-time" data-timestamp="<?= $item['timestamp'] ?>">
                                    <?= date('H:i:s', strtotime($item['timestamp'])) ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($item['details'])): ?>
                            <div class="activity-details">
                                <?php foreach ($item['details'] as $key => $value): ?
003e                                <div class="detail-item">
                                    <span class="detail-label"><?= $key ?>:</span>
                                    <span class="detail-value" title="<?= htmlspecialchars($value) ?>">
                                        <?= htmlspecialchars($value) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($older) > 0): ?>
                    <div class="collapsed-items" id="collapsed-<?= $agent ?>">
                        <?php foreach ($older as $item): ?
003e                        <div class="activity-item" data-timestamp="<?= $item['timestamp'] ?>">
                            <!-- Same structure as above -->
                            <div class="activity-icon <?= $item['type'] ?>" style="color: <?= $agentData['color'] ?>">
                                <?= $icons[$item['type']] ?? '⚡' ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-meta">
                                    <span class="activity-agent" style="color: <?= $agentData['color'] ?>"><?= $item['agentDisplay'] ?></span>
                                    <span class="activity-action"><?= $item['action'] ?></span>
                                    <span class="activity-time"><?= date('H:i:s', strtotime($item['timestamp'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?
003e                    </div>
                    <button class="expand-toggle" onclick="toggleExpand('<?= $agent ?>')">
                        <span>+ <?= count($older) ?> more from <?= ucfirst($agent) ?></span>
                        <span class="arrow">▼</span>
                    </button>
                    <?php endif; ?
003e                
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        Mission Control Observatory &middot; Real-time agent activity feed
    </footer>

    <script>
        // Configuration
        const POLL_INTERVAL = 5000; // 5 seconds
        const ACTIVITY_LOG_URL = '/logs/activity.md';
        
        // State
        let activities = [];
        let autoScroll = false;
        let lastContent = '';
        
        // Agent colors
        const agentColors = {
            minion: '#3b82f6', scout: '#22c55e', sage: '#a855f7',
            quill: '#f59e0b', xalt: '#ec4899', observer: '#64748b'
        };
        
        const typeIcons = {
            system: '⚡', search: '🔍', complete: '✅', message: '💬'
        };
        
        // Parse activity log content
        function parseActivityLog(content) {
            const entries = [];
            const sections = content.split('\n---\n');
            
            for (const section of sections) {
                const match = section.match(/^##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(.+?)\s+(.+)$/m);
                if (!match) continue;
                
                const [, timestamp, agent, action] = match;
                const details = {};
                
                const detailMatches = section.matchAll(/^- \*\*(.+?):\*\*\s*(.+)$/gm);
                for (const dm of detailMatches) {
                    details[dm[1]] = dm[2];
                }
                
                let type = 'system';
                if (action.includes('search')) type = 'search';
                else if (action.includes('completed')) type = 'complete';
                
                entries.push({
                    timestamp,
                    agent: agent.toLowerCase(),
                    agentDisplay: agent,
                    action,
                    type,
                    details
                });
            }
            
            return entries.reverse();
        }
        
        // Update agent status cards
        function updateAgentStatus(activities) {
            const agentLastActivity = {};
            
            for (const activity of activities) {
                if (!agentLastActivity[activity.agent] || 
                    activity.timestamp > agentLastActivity[activity.agent].timestamp) {
                    agentLastActivity[activity.agent] = activity;
                }
            }
            
            for (const [agent, lastActivity] of Object.entries(agentLastActivity)) {
                const card = document.querySelector(`[data-agent="${agent}"]`);
                if (!card) continue;
                
                const isWorking = lastActivity.action.includes('received') || 
                                  lastActivity.action.includes('delegated') ||
                                  lastActivity.action.includes('search') ||
                                  lastActivity.action.includes('filtered');
                
                const dot = document.getElementById(`status-dot-${agent}`);
                const text = document.getElementById(`status-text-${agent}`);
                const lastAction = document.getElementById(`last-action-${agent}`);
                
                if (isWorking) {
                    card.classList.add('active');
                    dot.classList.add('working');
                    dot.classList.remove('idle');
                    text.classList.add('working');
                    text.textContent = 'Working';
                } else {
                    card.classList.remove('active');
                    dot.classList.remove('working');
                    dot.classList.add('idle');
                    text.classList.remove('working');
                    text.textContent = 'Idle';
                }
                
                lastAction.textContent = lastActivity.action;
                lastAction.title = `${lastActivity.action} at ${lastActivity.timestamp}`;
            }
        }
        
        // Create activity item HTML
        function createActivityItem(activity, isNew = false) {
            const color = agentColors[activity.agent] || '#64748b';
            const icon = typeIcons[activity.type] || '⚡';
            
            const detailsHtml = Object.entries(activity.details)
                .map(([k, v]) => `
                    <div class="detail-item">
                        <span class="detail-label">${k}:</span>
                        <span class="detail-value" title="${v}">${v}</span>
                    </div>
                `).join('');
            
            return `
                <div class="activity-item ${isNew ? 'new' : ''}" data-timestamp="${activity.timestamp}" data-agent="${activity.agent}">
                    <div class="activity-icon ${activity.type}" style="color: ${color}">${icon}</div>
                    <div class="activity-content">
                        <div class="activity-meta">
                            <span class="activity-agent" style="color: ${color}">${activity.agentDisplay}</span>
                            <span class="activity-action">${activity.action}</span>
                            <span class="activity-time">${activity.timestamp.split(' ')[1]}</span>
                        </div>
                        ${detailsHtml ? `<div class="activity-details">${detailsHtml}</div>` : ''}
                    </div>
                </div>
            `;
        }
        
        // Poll for updates
        async function pollForUpdates() {
            try {
                const response = await fetch(ACTIVITY_LOG_URL + '?t=' + Date.now());
                const content = await response.text();
                
                if (content !== lastContent) {
                    const newActivities = parseActivityLog(content);
                    const newItems = newActivities.filter(a => 
                        !activities.some(existing => existing.timestamp === a.timestamp)
                    );
                    
                    if (newItems.length > 0) {
                        const list = document.getElementById('activityList');
                        
                        for (const item of newItems.reverse()) {
                            const html = createActivityItem(item, true);
                            list.insertAdjacentHTML('afterbegin', html);
                        }
                        
                        activities = newActivities;
                        updateAgentStatus(activities);
                        
                        if (autoScroll) {
                            list.scrollTop = 0;
                        }
                        
                        // Remove 'new' class after animation
                        setTimeout(() => {
                            document.querySelectorAll('.activity-item.new').forEach(el => {
                                el.classList.remove('new');
                            });
                        }, 3000);
                    }
                    
                    lastContent = content;
                    document.getElementById('connectionStatus').textContent = 'Connected';
                    document.getElementById('connectionStatus').classList.add('connected');
                }
            } catch (err) {
                console.error('Poll error:', err);
                document.getElementById('connectionStatus').textContent = 'Disconnected';
                document.getElementById('connectionStatus').classList.remove('connected');
            }
        }
        
        // Toggle auto-scroll
        function toggleAutoScroll() {
            autoScroll = !autoScroll;
            const btn = document.getElementById('autoScrollBtn');
            btn.classList.toggle('active', autoScroll);
        }
        
        // Manual refresh
        function manualRefresh() {
            pollForUpdates();
        }
        
        // Toggle expand
        function toggleExpand(agent) {
            const collapsed = document.getElementById(`collapsed-${agent}`);
            const btn = collapsed.nextElementSibling;
            
            collapsed.classList.toggle('expanded');
            btn.classList.toggle('expanded');
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            activities = parseActivityLog(document.body.dataset.initialContent || '');
            updateAgentStatus(activities);
            
            // Start polling
            setInterval(pollForUpdates, POLL_INTERVAL);
            
            // Initial poll after 1 second
            setTimeout(pollForUpdates, 1000);
        });
    </script>
</body>
</html>
