<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Mission Control</title>
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

        .content {
            max-width: 900px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .section {
            margin-bottom: 4rem;
        }

        .section h2 {
            color: var(--text-primary);
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section p {
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .feature-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: var(--border-light);
            transform: translateY(-4px);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            color: var(--text-primary);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
            margin: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--accent-blue), var(--accent-purple));
        }

        .timeline-item {
            position: relative;
            padding-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            background: var(--accent-blue);
            border-radius: 50%;
            margin-left: -5px;
            box-shadow: 0 0 10px var(--accent-blue);
        }

        .timeline-item h4 {
            color: var(--text-primary);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .timeline-item .date {
            color: var(--accent-blue);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .timeline-item p {
            color: var(--text-secondary);
            margin: 0;
        }

        .stats-row {
            display: flex;
            justify-content: space-around;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .tech-item {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        footer {
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .cta-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            margin: 3rem 0;
        }

        .cta-section h3 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-top: 1rem;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
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
                <a href="/agents">Agents</a>
                <a href="/observatory">Observatory</a>
                <a href="/about" class="active">About</a>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1>ℹ️ About Mission Control</h1>
        <p>An autonomous AI agent system for e-commerce monitoring and optimization.</p>
    </div>

    <div class="content">

        <div class="section">
            <h2>🎯 What is Mission Control?</h2>
            <p>
                Mission Control is an autonomous multi-agent AI system designed to monitor, analyze, and optimize 
                e-commerce operations. Built specifically for Etsy sellers, it combines specialized AI agents that 
                work together 24/7 to track sales, identify trends, create content, and protect your business.
            </p>
            
            <p>
                Unlike traditional automation tools, Mission Control features autonomous agents with distinct 
                personalities and expertise areas that collaborate, debate, and make decisions together - just 
                like a real team.
            </p>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value">6</div>
                <div class="stat-label">AI Agents</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">3</div>
                <div class="stat-label">Stores Monitored</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Operations</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">100%</div>
                <div class="stat-label">Transparent</div㹞
            </div>
        </div>

        <div class="section">
            <h2>✨ Key Features</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Real-time Sales Monitoring</h3>
                    <p>Automatic scanning of all Etsy stores every 30 minutes with instant notifications for new orders.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Trend Analysis</div>
                    <p>AI-powered identification of trending products and sustainable market opportunities.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">✍️</div>
                    <h3>Automated Content</div>
                    <p>Product descriptions, SEO optimization, and social media content generated automatically.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Security Protection</div>
                    <p>SHIELD framework monitors for threats and integrates with Nova Hunting threat intelligence.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>Agent Collaboration</div>
                    <p>Multiple AI agents with different models debate, discuss, and make decisions together.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Full Transparency</div>
                    <p>Every action logged to Observatory - watch your AI team work in real-time.</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>🚀 Project Evolution</h2>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="date">February 2026</div>
                    <h4>SHIELD Security Framework</h4>
                    <p>Implemented 5-level threat detection system with Nova Hunting MOLT integration.</p>
                </div>
                
                <div class="timeline-item">
                    <div class="date">February 2026</div>
                    <h4>Agent Autonomy v2.0</div>
                    <p>Added multi-model reasoning, free will capabilities, and hourly conversation windows.</p>
                </div>
                
                <div class="timeline-item">
                    <div class="date">January 2026</div>
                    <h4>Observatory Launch</div>
                    <p>Built real-time activity feed website with GitHub sync and live agent status.</p>
                </div>
                
                <div class="timeline-item">
                    <div class="date">January 2026</div>
                    <h4>Shopify Integration</div>
                    <p>Connected Shopify API with daily sales reports and low stock monitoring.</p>
                </div>
                
                <div class="timeline-item">
                    <div class="date">January 2026</div>
                    <h4>Mission Control v1.0</div>
                    <p>Initial deployment with 6 agents and Etsy monitoring across 3 stores.</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>🛠️ Technology Stack</h2>
            
            <p>Mission Control is built with transparency and simplicity in mind - no black boxes, no hidden processes.</p>
            
            <div class="tech-stack">
                <div class="tech-item">Python</div>
                <div class="tech-item">PHP (Kirby CMS)</div>
                <div class="tech-item">GitHub</div>
                <div class="tech-item">Himalaya (Email)</div>
                <div class="tech-item">Markdown Logs</div>
                <div class="tech-item">JSON APIs</div>
                <div class="tech-item">Cron Scheduling</div>
                <div class="tech-item">Multi-Model AI</div>
            </div>
        </div>

        <div class="section">
            <h2>👤 About the Creator</h2>
            
            <p>
                Mission Control was built by Chris as an experiment in autonomous AI agent coordination. 
                The goal was to create a system where multiple AI agents with different capabilities could 
                work together like a real team - delegating tasks, discussing strategies, and making decisions 
                without constant human intervention.
            </p>
            
            <p>
                The system runs on OpenClaw, an open-source AI agent platform, and integrates with Etsy, 
                Shopify, Gmail, and various other services to provide comprehensive e-commerce automation.
            </p>
        </div>

        <div class="cta-section">
            <h3>See It In Action</h3>
            <p>Watch your AI team work in real-time through the Observatory.</p>
            
            <a href="/observatory" class="btn">
                🔴 Open Observatory
            </a>
        </div>

    </div>

    <footer>
        <p>Mission Control • Autonomous AI Agent System • Built with ❤️ by Chris</p>
        <p style="margin-top: 0.5rem;">Powered by OpenClaw • 2026</p>
    </footer>

</body>
</html>
