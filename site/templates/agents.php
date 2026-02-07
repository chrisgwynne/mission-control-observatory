<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agents | Mission Control</title>
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
        }

        body {
            font-family: 'SF Mono', Monaco, 'Fira Code', monospace;
            background: var(--bg-primary);
            color: var(--text-secondary);
            line-height: 1.6;
            min-height: 100vh;
        }

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

        .page-header {
            padding: 8rem 2rem 3rem;
            text-align: center;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
        }

        .page-header h1 {
            font-size: 3rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .page-header p {
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .agents-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .agent-full-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .agent-full-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--agent-color);
            box-shadow: 0 0 20px var(--agent-color);
        }

        .agent-full-card:hover {
            border-color: var(--border-light);
            transform: translateY(-2px);
        }

        .agent-header {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .agent-avatar {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: var(--agent-color);
            flex-shrink: 0;
        }

        .agent-title h2 {
            color: var(--text-primary);
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
        }

        .agent-title .role {
            color: var(--agent-color);
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .agent-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--accent-green);
            margin-top: 0.5rem;
        }

        .agent-status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .agent-description {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .agent-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
        }

        .detail-label {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .expertise-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .tag {
            padding: 0.375rem 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .current-task {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .current-task-label {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }

        .current-task-value {
            color: var(--text-primary);
        }

        footer {
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

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
    </style>
</head>
<body>
    <nav>
        <div class="nav-content">
            <a href="/" class="logo">
                <div class="logo-icon">MC</div>
                <span>Mission Control</span>
            </a>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/agents" class="active">Agents</a>
                <a href="/observatory">Observatory</a>
                <a href="/about">About</a>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1>🎯 Meet Your AI Team</h1>
        <p>Six specialized autonomous agents working together 24/7 to monitor, analyze, and optimize your business.</p>
    </div>

    <div class="agents-container">

        <!-- Minion -->
        <div class="agent-full-card" style="--agent-color: #6366f1;">
            <div class="agent-header">
                <div class="agent-avatar">🎯</div>
                <div class="agent-title">
                    <h2>Minion</h2>
                    <div class="role">Chief of Staff / Coordinator</div>
                    <div class="agent-status-badge">Active</div>
                </div>
            </div>
            
            <p class="agent-description">
                Minion is the central coordinator for Mission Control. He receives tasks, delegates to appropriate agents, 
                manages workflow priorities, and ensures smooth handoffs between team members. When you need something done, 
                Minion figures out who should do it and makes sure it happens.
            </p>
            
            <div class="agent-details">
                <div class="detail-box">
                    <div class="detail-label">Model</div>
                    <div class="detail-value">Claude Opus 4</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Primary Function</div>
                    <div class="detail-value">Task Coordination</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Reports To</div>
                    <div class="detail-value">Chris (You)</div>
                </div>
            </div>
            
            <div class="expertise-tags">
                <span class="tag">Task Routing</span>
                <span class="tag">Workflow Management</span>
                <span class="tag">Priority Setting</span>
                <span class="tag">Agent Handoff</span>
                <span class="tag">Incident Response</span>
            </div>

            <div class="current-task">
                <div class="current-task-label">Current Focus</div>
                <div class="current-task-value">Coordinating task pipeline between Scout's trend analysis and Quill's content creation</div>
            </div>
        </div>

        <!-- Scout -->
        <div class="agent-full-card" style="--agent-color: #22c55e;">
            <div class="agent-header">
                <div class="agent-avatar">🔍</div>
                <div class="agent-title">
                    <h2>Scout</h2>
                    <div class="role">Head of Growth / Trend Hunter</div>
                    <div class="agent-status-badge" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); color: var(--accent-green);">Working</div>
                </div>
            </div>
            
            <p class="agent-description">
                Scout is your eyes and ears on the market. He monitors Etsy sales across all three stores (3D Print Dept, 
                Sign Stash, StickiPig), tracks trending products, analyzes competitor activity, and identifies new opportunities. 
                He's obsessed with finding what's working and why.
            </p>
            
            <div class="agent-details">
                <div class="detail-box">
                    <div class="detail-label">Model</div>
                    <div class="detail-value">GPT-4o</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Stores Monitored</div>
                    <div class="detail-value">3 (Print Dept, Sign Stash, StickiPig)</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Scan Frequency</div>
                    <div class="detail-value">Every 30 minutes</div>
                </div>
            </div>
            
            <div class="expertise-tags">
                <span class="tag">Sales Monitoring</span>
                <span class="tag">Trend Detection</span>
                <span class="tag">Market Research</span>
                <span class="tag">Competitor Analysis</span>
                <span class="tag">Email Integration</span>
            </div>

            <div class="current-task">
                <div class="current-task-label">Current Focus</div>
                <div class="current-task-value">Monitoring cycling print trends - 4 orders today, analyzing sustainability</div>
            </div>
        </div>

        <!-- Sage -->
        <div class="agent-full-card" style="--agent-color: #a855f7;">
            <div class="agent-header">
                <div class="agent-avatar">🧠</div>
                <div class="agent-title">
                    <h2>Sage</h2>
                    <div class="role">Head of Research / Analyst</div>
                    <div class="agent-status-badge" style="background: rgba(96, 96, 112, 0.1); border-color: rgba(96, 96, 112, 0.3); color: var(--text-muted);">Idle</div>
                </div>
            </div>
            
            <p class="agent-description">
                Sage provides the analytical backbone of Mission Control. He validates Scout's trend findings, 
                performs deep-dive research, conducts weekly product rankings, and handles security analysis. 
                When you need to know if a trend is real or just noise, Sage has the answer.
            </p>
            
            <div class="agent-details">
                <div class="detail-box">
                    <div class="detail-label">Model</div>
                    <div class="detail-value">Claude Sonnet 4</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Analysis Type</div>
                    <div class="detail-value">Statistical / Validation</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Security Role</div>
                    <div class="detail-value">Threat Analysis</div>
                </div>
            </div>
            
            <div class="expertise-tags">
                <span class="tag">Data Validation</span>
                <span class="tag">Statistical Analysis</span>
                <span class="tag">Trend Verification</span>
                <span class="tag">Security Research</span>
                <span class="tag">Weekly Reports</span>
            </div>

            <div class="current-task">
                <div class="current-task-label">Current Focus</div>
                <div class="current-task-value">Preparing weekly product ranking analysis for Sunday</div>
            </div>
        </div>

        <!-- Quill -->
        <div class="agent-full-card" style="--agent-color: #f59e0b;">
            <div class="agent-header">
                <div class="agent-avatar">✍️</div>
                <div class="agent-title">
                    <h2>Quill</h2>
                    <div class="role">Creative Director / Writer</div>
                    <div class="agent-status-badge" style="background: rgba(96, 96, 112, 0.1); border-color: rgba(96, 96, 112, 0.3); color: var(--text-muted);">Idle</div>
                </div>
            </div>
            
            <p class="agent-description">
                Quill is your creative powerhouse. He writes product descriptions, optimizes content for SEO, 
                maintains brand voice consistency, and creates compelling copy that converts. Every word on your 
                store has been crafted by Quill to maximize appeal and search visibility.
            </p>
            
            <div class="agent-details">
                <div class="detail-box">
                    <div class="detail-label">Model</div>
                    <div class="detail-value">GPT-4o</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Specialty</div>
                    <div class="detail-value">Product Descriptions</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">SEO Focus</div>
                    <div class="detail-value">Daily Optimization</div>
                </div>
            </div>
            
            <div class="expertise-tags">
                <span class="tag">Copywriting</span>
                <span class="tag">SEO Optimization</span>
                <span class="tag">Brand Voice</span>
                <span class="tag">Product Content</span>
                <span class="tag">Approval Workflow</span>
            </div>

            <div class="current-task">
                <div class="current-task-label">Current Focus</div>
                <div class="current-task-value">Working on Liverpool print descriptions - 9 of 12 complete</div>
            </div>
        </div>

        <!-- Xalt -->
        <div class="agent-full-card" style="--agent-color: #ec4899;">
            <div class="agent-header">
                <div class="agent-avatar">📱</div>
                <div class="agent-title">
                    <h2>Xalt</h2>
                    <div class="role">Social Media Director</div>
                    <div class="agent-status-badge" style="background: rgba(96, 96, 112, 0.1); border-color: rgba(96, 96, 112, 0.3); color: var(--text-muted);">Idle</div>
                </div>
            </div>
            
            <p class="agent-description">
                Xalt handles your social media presence across all platforms. He creates engaging posts, 
                manages content calendars, tracks engagement metrics, and knows exactly what your audience 
                wants to see. When Scout finds a trend, Xalt knows how to turn it into viral content.
            </p>
            
            <div class="agent-details">
                <div class="detail-box">
                    <div class="detail-label">Model</div>
                    <div class="detail-value">Gemini 2.5 Pro</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Platforms</div>
                    <div class="detail-value">Twitter, Instagram, Facebook</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Content Type</div>
                    <div class="detail-value">Product Posts / Campaigns</div>
                </div>
            </div>
            
            <div class="expertise-tags">
                <span class="tag">Social Posts</span>
                <span class="tag">Engagement Strategy</span>
                <span class="tag">Content Calendar</span>
                <span class="tag">Viral Marketing</span>
                <span class="tag">Audience Growth</span>
            </div>

            <div class="current-task">
                <div class="current-task-label">Current Focus</div>
                <div class="current-task-value">Standing by for Quill's content to generate social snippets</div>
            </div>
        </div>

        <!-- Observer -->
        <div class="agent-full-card" style="--agent-color: #06b6d4;">
            <div class="agent-header">
                <div class="agent-avatar">👁️</div>
                <div class="agent-title">
                    <h2>Observer</h2>
                    <div class="role">Operations Analyst / Monitor</div>
                    <div class="agent-status-badge" style="background: rgba(96, 96, 112, 0.1); border-color: rgba(96, 96, 112, 0.3); color: var(--text-muted);">Idle</div>
                </div>
            </div>
            
            <p class="agent-description">
                Observer is the guardian of Mission Control. He monitors system health, tracks agent status, 
                manages the Observatory website, and implements security protocols via SHIELD. When something 
                goes wrong or looks suspicious, Observer is the first to know and respond.
            </p>
            
            <div class="agent-details">
                <div class="detail-box">
                    <div class="detail-label">Model</div>
                    <div class="detail-value">Claude 3.7</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Monitoring</div>
                    <div class="detail-value">24/7 System Health</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Security</div>
                    <div class="detail-value">SHIELD Protocol</div>
                </div>
            </div>
            
            <div class="expertise-tags">
                <span class="tag">System Monitoring</span>
                <span class="tag">Health Checks</span>
                <span class="tag">Observatory</span>
                <span class="tag">Threat Detection</span>
                <span class="tag">Incident Response</span>
            </div>

            <div class="current-task">
                <div class="current-task-label">Current Focus</div>
                <div class="current-task-value">Monitoring Observatory sync status and system health checks</div>
            </div>
        </div>

    </div>

    <footer>
        <p>Mission Control • Autonomous AI Agent System • 2026</p>
    </footer>

</body>
</html>
