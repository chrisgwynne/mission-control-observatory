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
        elseif (stripos($action, 'completed') !== false) $type = 'complete';
        
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
            --bg-primary: #0a0a0f; --bg-secondary: #0f172a; --bg-tertiary: #1e293b;
            --border: #334155; --text-primary: #f8fafc; --text-secondary: #e2e8f0;
            --text-muted: #94a3b8; --text-dim: #64748b;
            --accent-blue: #3b82f6; --accent-green: #22c55e; --accent-purple: #a855f7;
            --accent-amber: #f59e0b; --accent-pink: #ec4899; --accent-slate: #64748b;
        }
        body {
            font-family: 'SF Mono', Monaco, Inconsolata, 'Fira Code', monospace;
            background: var(--bg-primary); color: var(--text-secondary);
            line-height: 1.6; min-height: 100vh;
        }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: repeating-linear-gradient(0deg, rgba(0,0,0,0.15) 0px, rgba(0,0,0,0.15) 1px, transparent 1px, transparent 2px);
            pointer-events: none; z-index: 1000;
        }
        .site-header {
            background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            border-bottom: 1px solid var(--border); padding: 1rem 2rem;
            position: sticky; top: 0; z-index: 100;
        }
        .header-content { max-width: 1400px; margin: 0 auto; display: flex; align-items: center; gap: 1rem; }
        .logo { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-primary); }
        .logo-icon {
            width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 14px; box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }
        .nav-links { margin-left: auto; display: flex; gap: 2rem; }
        .nav-links a { color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .nav-links a:hover { color: var(--text-primary); }
        .main-content { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; }
        .page-title { font-size: 2rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 0.75rem; }
        .live-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.375rem 0.75rem; background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 20px;
            font-size: 0.7rem; font-weight: 600; color: var(--accent-green); text-transform: uppercase;
        }
        .live-badge::before {
            content: ''; width: 6px; height: 6px; background: var(--accent-green);
            border-radius: 50%; animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        .connection-status { font-size: 0.8rem; color: var(--text-dim); }
        .connection-status.connected { color: var(--accent-green); }
        
        /* Agent Cards */
        .agent-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .agent-card {
            background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px;
            padding: 1.25rem; transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .agent-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--agent-color, var(--accent-blue)); opacity: 0.5;
        }
        .agent-card.active {
            border-color: var(--agent-color, var(--accent-blue));
            box-shadow: 0 0 30px rgba(0,0,0,0.3), 0 0 0 1px var(--agent-color, var(--accent-blue));
        }
        .agent-card.active::before { opacity: 1; box-shadow: 0 0 20px var(--agent-color, var(--accent-blue)); }
        .agent-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
        .agent-avatar {
            width: 40px; height: 40px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; font-size: 1.25rem;
            background: var(--agent-color, var(--accent-blue)); opacity: 0.9;
        }
        .agent-info h3 { color: var(--text-primary); font-size: 1rem; font-weight: 600; }
        .agent-info .role { font-size: 0.75rem; color: var(--text-dim); }
        .agent-status { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.75rem; font-size: 0.8rem; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--text-dim); }
        .status-dot.working { background: var(--accent-green); animation: pulse 1s infinite; }
        .status-text { color: var(--text-muted); }
        .status-text.working { color: var(--accent-green); }
        .last-action { margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        /* Activity Feed */
        .feed-container { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        .feed-header {
            background: var(--bg-tertiary); padding: 1rem 1.5rem;
            display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);
        }
        .feed-title { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); text-transform: uppercase; }
        .feed-controls { display: flex; gap: 0.75rem; }
        .control-btn {
            background: var(--bg-secondary); border: 1px solid var(--border); color: var(--text-muted);
            padding: 0.375rem 0.75rem; border-radius: 6px; font-family: inherit;
            font-size: 0.75rem; cursor: pointer; transition: all 0.2s;
        }
        .control-btn:hover { border-color: var(--text-muted); color: var(--text-secondary); }
        .control-btn.active { background: var(--accent-blue); border-color: var(--accent-blue); color: white; }
        .activity-list { max-height: 60vh; overflow-y: auto; padding: 0.5rem 0; }
        .activity-item {
            display: flex; gap: 1rem; padding: 0.875rem 1.5rem;
            border-bottom: 1px solid var(--border); transition: all 0.3s ease;
            animation: fadeIn 0.5s ease forwards;
        }
        .activity-item:hover { background: rgba(255,255,255,0.02); }
        .activity-item.new { background: rgba(34, 197, 94, 0.05); border-left: 3px solid var(--accent-green); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .activity-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .activity-icon.system { background: rgba(59, 130, 246, 0.15); }
        .activity-icon.search { background: rgba(34, 197, 94, 0.15); }
        .activity-icon.complete { background: rgba(168, 85, 247, 0.15); }
        .activity-content { flex: 1; min-width: 0; }
        .activity-meta { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; }
        .activity-agent { font-size: 0.8rem; font-weight: 600; }
        .activity-action { font-size: 0.85rem; color: var(--text-secondary); }
        .activity-time { margin-left: auto; font-size: 0.75rem; color: var(--text-dim); }
        .activity-details { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 0.5rem; font-size: 0.75rem; }
        .detail-item { display: flex; gap: 0.375rem; }
        .detail-label { color: var(--text-dim); }
        .detail-value { color: var(--text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        /* Expandable */
        .expand-toggle {
            width: 100%; padding: 0.75rem 1.5rem; background: transparent; border: none;
            color: var(--text-dim); font-family: inherit; font-size: 0.8rem;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .expand-toggle:hover { color: var(--text-secondary); background: rgba(255,255,255,0.02); }
        .collapsed-items { display: none; }
        .collapsed-items.expanded { display: block; }
        
        .site-footer { text-align: center; padding: 2rem; color: var(--text-dim); font-size: 0.8rem; border-top: 1px solid var(--border); margin-top: 2rem; }
        .activity-list::-webkit-scrollbar { width: 8px; }
        .activity-list::-webkit-scrollbar-track { background: var(--bg-secondary); }
        .activity-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <a href="/" class="logo"><div class="logo-icon">MC</div><span>Mission Control</span></a>
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
            <h1 class="page-title">Observatory <span class="live-badge">Live</span></h1>
            <span id="connectionStatus" class="connection-status">Connecting...</span>
        </div>

        <!-- Agent Status Cards -->
        <div class="agent-grid">
            <?php foreach ($agents as $key => $agent): ?>
            <div class="agent-card" data-agent="<?= $key ?>" style="--agent-color: <?= $agent['color'] ?>">
                <div class="agent-header">
                    <div class="agent-avatar"><?= $agent['icon'] ?></div>
                    <div class="agent-info">
                        <h3><?= $agent['name'] ?></h3>
                        <span class="role"><?= $agent['role'] ?></span>
                    </div>
                </div>
                <div class="agent-status">
                    <span class="status-dot" id="status-<?= $key ?>"></span>
                    <span class="status-text" id="status-text-<?= $key ?>">Idle</span>
                </div>
                <div class="last-action" id="action-<?= $key ?>">Waiting...</div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Activity Feed -->
        <div class="feed-container">
            <div class="feed-header">
                <span class="feed-title">Activity Log</span>
                <div class="feed-controls">
                    <button class="control-btn" id="autoScrollBtn" onclick="toggleAutoScroll()">⬇ Auto-scroll</button>
                    <button class="control-btn" onclick="location.reload()">↻ Refresh</button>
                </div>
            </div>
            
            <div class="activity-list" id="activityList">
                <?php foreach (array_slice($activities, 0, 20) as $item): ?>
                <div class="activity-item" data-timestamp="<?= $item['timestamp'] ?>">
                    <?php $color = $agents[$item['agent']]['color'] ?? '#64748b'; ?>
                    <?php $icons = ['system' => '⚡', 'search' => '🔍', 'complete' => '✅']; ?>
                    
                    <div class="activity-icon <?= $item['type'] ?>" style="color: <?= $color ?>">
                        <?= $icons[$item['type']] ?? '⚡' ?>
                    </div>
                    
                    <div class="activity-content">
                        <div class="activity-meta">
                            <span class="activity-agent" style="color: <?= $color ?>"><?= $item['agentDisplay'] ?></span>
                            <span class="activity-action"><?= $item['action'] ?></span>
                            <span class="activity-time"><?= substr($item['timestamp'], 11) ?></span>
                        </div>
                        
                        <?php if (!empty($item['details'])): ?>
                        <div class="activity-details">
                            <?php foreach ($item['details'] as $k => $v): ?>
                            <div class="detail-item">
                                <span class="detail-label"><?= $k ?>:</span>
                                <span class="detail-value" title="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">Mission Control Observatory &middot; Real-time agent activity</footer>

    <script>
        const agents = ['minion','scout','sage','quill','xalt','observer'];
        const colors = { minion:'#3b82f6', scout:'#22c55e', sage:'#a855f7', quill:'#f59e0b', xalt:'#ec4899', observer:'#64748b' };
        const icons = { system:'⚡', search:'🔍', complete:'✅' };
        let autoScroll = false;
        
        function updateAgentStatus() {
            const items = document.querySelectorAll('.activity-item');
            const lastByAgent = {};
            
            items.forEach(item => {
                const agent = item.dataset.agent;
                if (!lastByAgent[agent]) lastByAgent[agent] = item;
            });
            
            agents.forEach(agent => {
                const card = document.querySelector(`[data-agent="${agent}"]`);
                const dot = document.getElementById(`status-${agent}`);
                const text = document.getElementById(`status-text-${agent}`);
                const action = document.getElementById(`action-${agent}`);
                
                if (lastByAgent[agent]) {
                    const actionText = lastByAgent[agent].querySelector('.activity-action').textContent;
                    const isWorking = /received|delegated|search|filtered/i.test(actionText);
                    
                    card.classList.toggle('active', isWorking);
                    dot.classList.toggle('working', isWorking);
                    dot.classList.toggle('idle', !isWorking);
                    text.classList.toggle('working', isWorking);
                    text.textContent = isWorking ? 'Working' : 'Idle';
                    action.textContent = actionText;
                }
            });
        }
        
        function toggleAutoScroll() {
            autoScroll = !autoScroll;
            document.getElementById('autoScrollBtn').classList.toggle('active', autoScroll);
        }
        
        // Initial status update
        updateAgentStatus();
        
        // Simulate real-time updates
        setInterval(() => {
            document.getElementById('connectionStatus').textContent = 'Connected';
            document.getElementById('connectionStatus').classList.add('connected');
        }, 1000);
    </script>
</body>
</html>
