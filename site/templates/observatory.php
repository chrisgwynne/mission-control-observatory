<?php
/**
 * Observatory Template - Real-time Live Activity Feed
 * Inspired by https://www.voxyz.space/stage
 */

// Parse activity log file
$activityFile = $kirby->root('content') . '/logs/activity.md';
$workspaceLog = '/home/chris/.openclaw/workspace/mission-control/logs/activity.md';
$agentStatusFile = '/home/chris/.openclaw/workspace/mission-control/logs/agent_status.json';

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

// Load agent status if available
$agentStatuses = [];
if (file_exists($agentStatusFile)) {
    $agentStatuses = json_decode(file_get_contents($agentStatusFile), true)['agents'] ?? [];
}

// Parse entries
$entries = preg_split('/\n---\n/', $content);
foreach ($entries as $entry) {
    $entry = trim($entry);
    if (empty($entry) || strpos($entry, '# Mission Control Activity Log') !== false) continue;
    
    // Check for hourly check-in conversations
    if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+🔄\s+Hourly Check-in:\s*(.+)$/m', $entry, $matches)) {
        $timestamp = $matches[1];
        $topic = $matches[2];
        
        // Extract participants
        preg_match('/\*\*Participants:\*\*\s*(.+)/m', $entry, $partMatch);
        $participants = $partMatch ? $partMatch[1] : 'Various agents';
        
        // Extract messages
        preg_match_all('/\*\*(\w+):\*\*\s*(.+?)(?=\n\*\*|$)/s', $entry, $msgMatches, PREG_SET_ORDER);
        $messages = [];
        foreach ($msgMatches as $mm) {
            $messages[] = ['agent' => strtolower($mm[1]), 'message' => trim($mm[2])];
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
        preg_match('/\*\*Action:\*\*\s*(.+)/m', $entry, $actionMatch);
        
        $activities[] = [
            'timestamp' => $timestamp,
            'agent' => 'observer',
            'agentDisplay' => '🛡️ Security',
            'action' => $typeMatch ? trim($typeMatch[1]) : 'Security Alert',
            'type' => 'security',
            'details' => [
                'Status' => $statusMatch ? trim($statusMatch[1]) : 'Unknown',
                'Action' => $actionMatch ? trim($actionMatch[1]) : 'Monitoring'
            ]
        ];
        continue;
    }
    
    // Check for sales updates
    if (preg_match('/##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+.*💰/i', $entry, $matches)) {
        $timestamp = $matches[1];
        
        preg_match('/\*\*Store:\*\*\s*(.+)/m', $entry, $storeMatch);
        preg_match('/\*\*Product:\*\*\s*(.+)/m', $entry, $prodMatch);
        preg_match('/\*\*Revenue:\*\*\s*(.+)/m', $entry, $revMatch);
        
        $activities[] = [
            'timestamp' => $timestamp,
            'agent' => 'scout',
            'agentDisplay' => '💰 Scout',
            'action' => 'New Sale Detected',
            'type' => 'sale',
            'details' => [
                'Store' => $storeMatch ? trim($storeMatch[1]) : 'Unknown',
                'Product' => $prodMatch ? trim($prodMatch[1]) : 'Unknown',
                'Revenue' => $revMatch ? trim($revMatch[1]) : 'Unknown'
            ]
        ];
        continue;
    }
    
    // Standard activity entry
    if (preg_match('/^##\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(.+?)\s+(.+)$/m', $entry, $matches)) {
        $timestamp = $matches[1];
        $agent = strtolower($matches[2]);
        $action = $matches[3];
        
        // Clean up agent name
        $agent = preg_replace('/[^a-z]/', '', $agent);
        
        $details = [];
        preg_match_all('/^\s*-\s+\*\*(.+?):\*\*\s*(.+)$/m', $entry, $detailMatches, PREG_SET_ORDER);
        foreach ($detailMatches as $dm) {
            $details[$dm[1]] = $dm[2];
        }
        
        // Determine activity type
        $type = 'system';
        if (stripos($action, 'search') !== false) $type = 'search';
        elseif (stripos($action, 'completed') !== false) $type = 'complete';
        elseif (stripos($action, 'detected') !== false) $type = 'detect';
        elseif (stripos($action, 'sale') !== false) $type = 'sale';
        
        $activities[] = [
            'timestamp' => $timestamp,
            'agent' => $agent,
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
    'minion' => ['name' => 'Minion', 'role' => 'Coordinator', 'model' => 'Claude Opus 4', 'color' => '#6366f1', 'icon' => '🎯'],
    'scout' => ['name' => 'Scout', 'role' => 'Trend Hunter', 'model' => 'GPT-4o', 'color' => '#22c55e', 'icon' => '🔍'],
    'sage' => ['name' => 'Sage', 'role' => 'Analyst', 'model' => 'Claude Sonnet 4', 'color' => '#a855f7', 'icon' => '🧠'],
    'quill' => ['name' => 'Quill', 'role' => 'Writer', 'model' => 'GPT-4o', 'color' => '#f59e0b', 'icon' => '✍️'],
    'xalt' => ['name' => 'Xalt', 'role' => 'Social Media', 'model' => 'Gemini 2.5 Pro', 'color' => '#ec4899', 'icon' => '📱'],
    'observer' => ['name' => 'Observer', 'role' => 'Monitor', 'model' => 'Claude 3.7', 'color' => '#06b6d4', 'icon' => '👁️']
];

// Get system status from activity log header
$systemStatus = '🟢 Operational';
$shieldStatus = '🛡️ Protected';
$activeAgents = 0;

if (preg_match('/\*\*System Status:\*\*\s*(.+)/m', $content, $m)) $systemStatus = trim($m[1]);
if (preg_match('/\*\*SHIELD Status:\*\*\s*(.+)/m', $content, $m)) $shieldStatus = trim($m[1]);

foreach ($agentStatuses as $key => $status) {
    if ($status['status'] !== 'asleep') $activeAgents++;
}
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
            --accent-blue: #6366f1; --accent-green: #22c55e; --accent-purple: #a855f7;
            --accent-amber: #f59e0b; --accent-pink: #ec4899; --accent-cyan: #06b6d4;
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
            font-weight: bold; font-size: 14px; box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }
        .status-bar {
            margin-left: auto; display: flex; align-items: center; gap: 1.5rem;
            font-size: 0.8rem; color: var(--text-muted);
        }
        .status-item { display: flex; align-items: center; gap: 0.5rem; }
        .nav-links { display: flex; gap: 2rem; }
        .nav-links a { color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .nav-links a:hover { color: var(--text-primary); }
        .main-content { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
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
        
        /* Agent Cards */
        .agent-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .agent-card {
            background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px;
            padding: 1.25rem; transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .agent-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--agent-color); opacity: 0.3;
        }
        .agent-card.working {
            border-color: var(--agent-color);
            box-shadow: 0 0 30px rgba(0,0,0,0.3), 0 0 0 1px var(--agent-color), 0 0 20px rgba(var(--agent-color-rgb), 0.2);
        }
        .agent-card.working::before { opacity: 1; box-shadow: 0 0 20px var(--agent-color); }
        .agent-card.awake::before { opacity: 0.6; }
        .agent-card.asleep { opacity: 0.7; }
        .agent-card.asleep::before { opacity: 0.1; }
        .agent-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
        .agent-avatar {
            width: 40px; height: 40px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; font-size: 1.25rem;
            background: var(--agent-color); opacity: 0.9;
        }
        .agent-info h3 { color: var(--text-primary); font-size: 1rem; font-weight: 600; }
        .agent-info .role { font-size: 0.75rem; color: var(--text-dim); }
        .agent-model { font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.5rem; }
        .agent-status { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; }
        .status-dot.working { background: var(--accent-green); animation: pulse 1s infinite; box-shadow: 0 0 8px var(--accent-green); }
        .status-dot.awake { background: var(--accent-amber); }
        .status-dot.asleep { background: var(--text-dim); }
        .status-text { text-transform: uppercase; font-size: 0.7rem; font-weight: 600; }
        .status-text.working { color: var(--accent-green); }
        .status-text.awake { color: var(--accent-amber); }
        .status-text.asleep { color: var(--text-dim); }
        .last-action { margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        /* Activity Feed */
        .feed-container { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        .feed-header {
            background: var(--bg-tertiary); padding: 1rem 1.5rem;
            display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);
        }
        .feed-title { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); text-transform: uppercase; display: flex; align-items: center; gap: 0.5rem; }
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
            display: flex; gap: 1rem; padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border); transition: all 0.3s ease;
            animation: fadeIn 0.5s ease forwards;
        }
        .activity-item:hover { background: rgba(255,255,255,0.02); }
        .activity-item.new { background: rgba(34, 197, 94, 0.05); border-left: 3px solid var(--accent-green); }
        .activity-item.security { background: rgba(239, 68, 68, 0.05); border-left: 3px solid #ef4444; }
        .activity-item.sale { background: rgba(34, 197, 94, 0.05); border-left: 3px solid var(--accent-green); }
        .activity-item.conversation { background: rgba(168, 85, 247, 0.05); border-left: 3px solid var(--accent-purple); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .activity-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .activity-icon.system { background: rgba(99, 102, 241, 0.15); }
        .activity-icon.search { background: rgba(34, 197, 94, 0.15); }
        .activity-icon.complete { background: rgba(168, 85, 247, 0.15); }
        .activity-icon.detect { background: rgba(6, 182, 212, 0.15); }
        .activity-icon.sale { background: rgba(34, 197, 94, 0.15); }
        .activity-icon.security { background: rgba(239, 68, 68, 0.15); }
        .activity-icon.conversation { background: rgba(168, 85, 247, 0.15); }
        .activity-content { flex: 1; min-width: 0; }
        .activity-meta { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; }
        .activity-agent { font-size: 0.8rem; font-weight: 600; }
        .activity-action { font-size: 0.85rem; color: var(--text-secondary); }
        .activity-time { margin-left: auto; font-size: 0.75rem; color: var(--text-dim); }
        .activity-details { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 0.5rem; font-size: 0.75rem; }
        .detail-item { display: flex; gap: 0.375rem; }
        .detail-label { color: var(--text-dim); }
        .detail-value { color: var(--text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        /* Conversation styling */
        .conversation-messages { margin-top: 0.5rem; padding: 0.75rem; background: var(--bg-primary); border-radius: 8px; }
        .conv-message { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; font-size: 0.8rem; }
        .conv-message:last-child { margin-bottom: 0; }
        .conv-agent { font-weight: 600; color: var(--text-muted); white-space: nowrap; }
        .conv-text { color: var(--text-secondary); }
        
        .site-footer { text-align: center; padding: 2rem; color: var(--text-dim); font-size: 0.8rem; border-top: 1px solid var(--border); margin-top: 2rem; }
        .activity-list::-webkit-scrollbar { width: 8px; }
        .activity-list::-webkit-scrollbar-track { background: var(--bg-secondary); }
        .activity-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        
        /* SHIELD badge */
        .shield-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.375rem 0.75rem; background: rgba(6, 182, 212, 0.15);
            border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 20px;
            font-size: 0.7rem; font-weight: 600; color: var(--accent-cyan);
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <a href="/" class="logo"><div class="logo-icon">MC</div><span>Mission Control</span></a>
            <div class="status-bar">
                <span class="status-item"><?= $systemStatus ?></span>
                <span class="status-item"><?= $shieldStatus ?></span>
                <span class="status-item"><?= $activeAgents ?>/6 Agents Active</span>
            </div>
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
            <span class="shield-badge">🛡️ SHIELD Active</span>
        </div>

        <!-- Agent Status Cards -->
        <div class="agent-grid">
            <?php foreach ($agents as $key => $agent): 
                $statusInfo = $agentStatuses[$key] ?? ['status' => 'asleep', 'current_task' => null];
                $status = $statusInfo['status'];
                $task = $statusInfo['current_task'] ?? 'No current task';
            ?>
            <div class="agent-card <?= $status ?>" style="--agent-color: <?= $agent['color'] ?>">
                <div class="agent-header">
                    <div class="agent-avatar"><?= $agent['icon'] ?></div>
                    <div class="agent-info">
                        <h3><?= $agent['name'] ?></h3>
                        <span class="role"><?= $agent['role'] ?></span>
                    </div>
                </div>
                <div class="agent-model"><?= $agent['model'] ?></div>
                <div class="agent-status">
                    <span class="status-dot <?= $status ?>"></span>
                    <span class="status-text <?= $status ?>"><?= strtoupper($status) ?></span>
                </div>
                <div class="last-action" title="<?= htmlspecialchars($task) ?>"><?= htmlspecialchars($task) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Activity Feed -->
        <div class="feed-container">
            <div class="feed-header">
                <span class="feed-title">📡 Activity Feed</span>
                <div class="feed-controls">
                    <button class="control-btn" onclick="location.reload()">↻ Refresh</button>
                </div>
            </div>
            
            <div class="activity-list" id="activityList">
                <?php foreach (array_slice($activities, 0, 25) as $item): 
                    $typeClass = $item['type'] ?? 'system';
                    $color = $agents[$item['agent']]['color'] ?? '#64748b';
                    $icons = [
                        'system' => '⚡', 'search' => '🔍', 'complete' => '✅', 
                        'detect' => '👁️', 'sale' => '💰', 'security' => '🛡️',
                        'conversation' => '🔄'
                    ];
                ?>
                <div class="activity-item <?= $typeClass ?>" data-timestamp="<?= $item['timestamp'] ?>">
                    <div class="activity-icon <?= $typeClass ?>" style="color: <?= $color ?>">
                        <?= $icons[$typeClass] ?? '⚡' ?>
                    </div>
                    
                    <div class="activity-content">
                        <div class="activity-meta">
                            <span class="activity-agent" style="color: <?= $color ?>"><?= $item['agentDisplay'] ?></span>
                            <span class="activity-action"><?= $item['action'] ?></span>
                            <span class="activity-time"><?= substr($item['timestamp'], 11, 5) ?></span>
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
                        
                        <?php if (!empty($item['messages'])): ?>
                        <div class="conversation-messages">
                            <?php foreach (array_slice($item['messages'], 0, 3) as $msg): 
                                $msgColor = $agents[$msg['agent']]['color'] ?? '#64748b';
                            ?>
                            <div class="conv-message">
                                <span class="conv-agent" style="color: <?= $msgColor ?>"><?= ucfirst($msg['agent']) ?>:</span>
                                <span class="conv-text"><?= htmlspecialchars(substr($msg['message'], 0, 100)) ?><?= strlen($msg['message']) > 100 ? '...' : '' ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($item['messages']) > 3): ?>
                            <div class="conv-message" style="color: var(--text-dim);">... and <?= count($item['messages']) - 3 ?> more messages</div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">Mission Control Observatory &middot; Real-time agent activity &middot; 🛡️ Protected by SHIELD</footer>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

