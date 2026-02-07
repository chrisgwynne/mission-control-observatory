<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission Control</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #050508;
            --bg-secondary: #0a0a0f;
            --bg-tertiary: #12121a;
            --border: #1e1e2e;
            --border-light: #2a2a3e;
            --text-primary: #ffffff;
            --text-secondary: #a0a0b0;
            --text-muted: #606070;
            --accent-blue: #3b82f6;
            --accent-green: #22c55e;
            --accent-purple: #a855f7;
            --accent-amber: #f59e0b;
            --accent-pink: #ec4899;
            --accent-cyan: #06b6d4;
            --accent-red: #ef4444;
        }

        body {
            font-family: 'SF Mono', Monaco, 'Fira Code', monospace;
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
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 0, 0, 0.15) 0px,
                rgba(0, 0, 0, 0.15) 1px,
                transparent 1px,
                transparent 2px
            );
            pointer-events: none;
            z-index: 1000;
        }

        /* Glow effect */
        .glow {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.15;
            pointer-events: none;
        }

        .glow-1 {
            background: var(--accent-blue);
            top: -200px;
            right: -200px;
        }

        .glow-2 {
            background: var(--accent-purple);
            bottom: -200px;
            left: -200px;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 1.5rem 3rem;
            background: linear-gradient(180deg, var(--bg-secondary) 0%, transparent 100%);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(10px);
        }

        .nav-content {
            max-width: 1400px;
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
            font-weight: 600;
            font-size: 1.1rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.4);
        }

        .nav-links {
            display: flex;
            gap: 3rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        .nav-links a.active {
            color: var(--accent-blue);
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent-blue);
            box-shadow: 0 0 10px var(--accent-blue);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 8rem 2rem 4rem;
            position: relative;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--accent-green);
            margin-bottom: 2rem;
        }

        .hero-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .hero h1 {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 50%, var(--accent-pink) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin-bottom: 3rem;
        }

        .hero-stats {
            display: flex;
            gap: 4rem;
            margin-top: 4rem;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-light);
        }

        .btn-secondary:hover {
            border-color: var(--accent-blue);
            color: var(--text-primary);
        }

        /* Live Activity Section */
        .live-activity {
            padding: 6rem 2rem;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border);
        }

        .section-header {
            max-width: 1400px;
            margin: 0 auto 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 20px;
            font-size: 0.75rem;
            color: var(--accent-green);
            text-transform: uppercase;
        }

        .activity-feed {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .activity-header {
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-icon.sale {
            background: rgba(34, 197, 94, 0.15);
            color: var(--accent-green);
        }

        .activity-icon.system {
            background: rgba(59, 130, 246, 0.15);
            color: var(--accent-blue);
        }

        .activity-icon.conversation {
            background: rgba(168, 85, 247, 0.15);
            color: var(--accent-purple);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .activity-meta {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .activity-time {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Agents Preview */
        .agents-preview {
            padding: 6rem 2rem;
        }

        .agents-grid {
            max-width: 1200px;
            margin: 3rem auto 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .agent-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .agent-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--agent-color, var(--accent-blue));
            opacity: 0.5;
        }

        .agent-card:hover {
            border-color: var(--border-light);
            transform: translateY(-4px);
        }

        .agent-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .agent-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--agent-color, var(--accent-blue));
        }

        .agent-info h3 {
            color: var(--text-primary);
            font-size: 1.1rem;
        }

        .agent-info p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .agent-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.85rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: pulse 2s infinite;
        }

        /* Footer */
        footer {
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--border-light);
        }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <nav>
        <div class="nav-content">
            <a href="/" class="logo">
                <div class="logo-icon">MC</div>
                <span>Mission Control</span>
            </a>
            <div class="nav-links">
                <a href="/" class="active">Home</a>
                <a href="/agents">Agents</a>
                <a href="/observatory">Observatory</a>
                <a href="/about">About</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-badge">
            <span></span>
            System Operational
        </div>
        <h1>
            Autonomous AI<br>
            <span>Mission Control</span>
        </h1>
        <p>
            A team of specialized AI agents working 24/7 to monitor sales, 
            analyze trends, create content, and optimize your business.
        </p>
        <div class="cta-buttons">
            <a href="/observatory" class="btn btn-primary">
                🔴 Live Observatory
            </a>
            <a href="/agents" class="btn btn-secondary">
                Meet the Team
            </a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <div class="stat-value">6</div>
                <div class="stat-label">Active Agents</div>
            </div>
            <div class="stat">
                <div class="stat-value">£209</div>
                <div class="stat-label">Sales Today</div>
            </div>
            <div class="stat">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Monitoring</div>
            </div>
        </div>
    </section>

    <section class="live-activity">
        <div class="section-header">
            <h2 class="section-title">
                📡 Live Activity
            </h2>
            <span class="live-indicator">
                <span></span>
                Live Feed
            </span>
        </div>
        <div class="activity-feed">
            <div class="activity-header">
                <span>Recent Events</span>
                <span style="color: var(--text-muted); font-size: 0.8rem;">Auto-refreshing</span>
            </div>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon sale">💰</div>
                    <div class="activity-content">
                        <div class="activity-title">New Sale Detected - StickiPig</div>
                        <div class="activity-meta">Order #3971887777 • £6.74</div>
                    </div>
                    <div class="activity-time">21:18</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon conversation">🔄</div>
                    <div class="activity-content">
                        <div class="activity-title">Hourly Check-in: Content Strategy</div>
                        <div class="activity-meta">Quill, Xalt, Minion • 5 messages</div>
                    </div>
                    <div class="activity-time">21:16</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon sale">💰</div>
                    <div class="activity-content">
                        <div class="activity-title">New Sale Detected - 3D Print Dept</div>
                        <div class="activity-meta">Order #3967890278 • £30.97</div>
                    </div>
                    <div class="activity-time">19:28</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon system">🛠️</div>
                    <div class="activity-content">
                        <div class="activity-title">Etsy Monitor Fix Applied</div>
                        <div class="activity-meta">Improved detection patterns</div>
                    </div>
                    <div class="activity-time">19:43</div>
                </div>
            </div>
        </div>
    </section>

    <section class="agents-preview">
        <div class="section-header" style="justify-content: center;">
            <h2 class="section-title">🎯 Meet Your Team</h2>
        </div>
        <div class="agents-grid">
            <div class="agent-card" style="--agent-color: #6366f1;">
                <div class="agent-header">
                    <div class="agent-avatar">🎯</div>
                    <div class="agent-info">
                        <h3>Minion</h3>
                        <p>Coordinator</p>
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Coordinates all agent activities, delegates tasks, and ensures smooth operations across the team.
                </p>
                <div class="agent-status">
                    <div class="status-dot"></div>
                    <span style="color: var(--accent-green);">Active</span>
                </div>
            </div>
            <div class="agent-card" style="--agent-color: #22c55e;">
                <div class="agent-header">
                    <div class="agent-avatar">🔍</div>
                    <div class="agent-info">
                        <h3>Scout</h3>
                        <p>Trend Hunter</p>
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Monitors Etsy sales, tracks market trends, and identifies new opportunities for growth.
                </p>
                <div class="agent-status">
                    <div class="status-dot"></div>
                    <span style="color: var(--accent-green);">Scanning</span>
                </div>
            </div>
            <div class="agent-card" style="--agent-color: #a855f7;">
                <div class="agent-header">
                    <div class="agent-avatar">🧠</div>
                    <div class="agent-info">
                        <h3>Sage</h3>
                        <p>Analyst</p>
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Validates data, performs deep analysis, and provides strategic insights for decision-making.
                </p>
                <div class="agent-status">
                    <div class="status-dot" style="background: var(--text-muted);"></div>
                    <span style="color: var(--text-muted);">Idle</span>
                </div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 3rem;">
            <a href="/agents" class="btn btn-secondary">View All Agents</a>
        </div>
    </section>

    <footer>
        <p>Mission Control • Autonomous AI Agent System • 2026</p>
        <p style="margin-top: 0.5rem;">🔴 Live • 🛡️ Protected • 6 Agents Active</p>
    </footer>

    <script>
        // Auto-refresh activity feed every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
