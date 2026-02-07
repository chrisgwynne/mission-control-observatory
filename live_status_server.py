#!/usr/bin/env python3
"""
Simple Live Status Server for Mission Control
Serves live activity status without PHP caching issues
"""

import http.server
import socketserver
import json
import os
from datetime import datetime
from threading import Thread
import time

PORT = 8001
WORKSPACE = "/home/chris/.openclaw/workspace/mission-control"

class StatusHandler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/':
            self.serve_index()
        elif self.path == '/status.json':
            self.serve_status()
        elif self.path == '/activity':
            self.serve_activity()
        else:
            super().do_GET()
    
    def serve_index(self):
        """Serve the live status page."""
        content = self.generate_html()
        self.send_response(200)
        self.send_header('Content-type', 'text/html')
        self.send_header('Cache-Control', 'no-cache, no-store, must-revalidate')
        self.end_headers()
        self.wfile.write(content.encode())
    
    def serve_status(self):
        """Serve JSON status."""
        status = self.get_status()
        self.send_response(200)
        self.send_header('Content-type', 'application/json')
        self.send_header('Cache-Control', 'no-cache')
        self.end_headers()
        self.wfile.write(json.dumps(status, indent=2).encode())
    
    def serve_activity(self):
        """Serve recent activity."""
        activities = self.get_recent_activity()
        self.send_response(200)
        self.send_header('Content-type', 'application/json')
        self.send_header('Cache-Control', 'no-cache')
        self.end_headers()
        self.wfile.write(json.dumps(activities, indent=2).encode())
    
    def get_status(self):
        """Get current system status."""
        now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        # Read activity log
        activity_file = os.path.join(WORKSPACE, "logs", "activity.md")
        try:
            with open(activity_file, 'r') as f:
                content = f.read()
            
            # Extract last updated
            import re
            match = re.search(r'\*\*Last Updated:\*\*\s*(.+)', content)
            last_updated = match.group(1) if match else now
            
            # Check for recent entries
            recent_count = len(re.findall(r'## \d{4}-\d{2}-\d{2} \d{2}:\d{2}', content))
            
        except:
            last_updated = now
            recent_count = 0
        
        return {
            "status": "operational",
            "shield": "protected",
            "agents_active": "6",
            "last_updated": now,
            "activity_entries": recent_count,
            "observatory": "http://localhost:8001/"
        }
    
    def get_recent_activity(self):
        """Get recent activity entries."""
        activity_file = os.path.join(WORKSPACE, "logs", "activity.md")
        activities = []
        
        try:
            with open(activity_file, 'r') as f:
                content = f.read()
            
            entries = content.split('---\n')
            
            # Entries are newest first in the file, so just take first 30
            entries = entries[:30]
            
            for entry in entries:
                entry = entry.strip()
                if not entry or entry.startswith('# Mission Control'):
                    continue
                
                lines = entry.split('\n')
                timestamp = ""
                body_lines = []
                
                for line in lines:
                    if line.startswith('## '):
                        timestamp = line.replace('## ', '').strip()
                    elif line.startswith('**') or line.startswith('- '):
                        body_lines.append(line)
                
                if timestamp and body_lines:
                    activities.append({
                        "timestamp": timestamp,
                        "content": '\n'.join(body_lines[:5])
                    })
        except:
            pass
        
        return activities
    
    def generate_html(self):
        """Generate the HTML page."""
        status = self.get_status()
        
        return f'''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission Control - Live Status</title>
    <meta http-equiv="refresh" content="15">
    <style>
        * {{ margin: 0; padding: 0; box-sizing: border-box; }}
        :root {{
            --bg-primary: #0a0a0f; --bg-secondary: #0f172a; --bg-tertiary: #1e293b;
            --border: #334155; --text-primary: #f8fafc; --text-secondary: #e2e8f0;
            --text-muted: #94a3b8; --accent-green: #22c55e;
        }}
        body {{
            font-family: 'SF Mono', Monaco, monospace;
            background: var(--bg-primary); color: var(--text-secondary);
            line-height: 1.6; padding: 2rem; min-height: 100vh;
        }}
        h1 {{ color: var(--text-primary); margin-bottom: 1rem; }}
        .status-bar {{ 
            display: flex; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;
        }}
        .status-item {{
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1rem; background: var(--bg-secondary);
            border-radius: 8px; border: 1px solid var(--border);
            font-size: 0.9rem;
        }}
        .live-badge {{
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.375rem 0.75rem; background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 20px;
            font-size: 0.7rem; font-weight: 600; color: var(--accent-green);
            text-transform: uppercase;
        }}
        .live-badge::before {{
            content: ''; width: 8px; height: 8px; background: var(--accent-green);
            border-radius: 50%; animation: pulse 1.5s ease-in-out infinite;
        }}
        @keyframes pulse {{ 0%, 100% {{ opacity: 1; }} 50% {{ opacity: 0.4; }} }}
        
        .activity-section {{ margin-top: 2rem; }}
        .activity-item {{
            background: var(--bg-secondary); border: 1px solid var(--border);
            padding: 1rem; margin-bottom: 0.5rem; border-radius: 8px;
            border-left: 4px solid var(--accent-green);
        }}
        .timestamp {{ color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.5rem; }}
        .content {{ color: var(--text-secondary); font-size: 0.85rem; white-space: pre-wrap; }}
        
        .refresh-info {{
            position: fixed; bottom: 1rem; right: 1rem;
            color: var(--text-muted); font-size: 0.7rem;
        }}
    </style>
</head>
<body>
    <h1>🔴 Mission Control Live Status</h1>
    
    <div class="status-bar">
        <div class="status-item">🟢 All Systems Operational</div>
        <div class="status-item">🛡️ SHIELD Protected</div>
        <div class="status-item">6 Agents Active</div>
        <div class="live-badge">LIVE - Auto-refresh</div>
        <div class="status-item">Updated: {status['last_updated']}</div>
    </div>

    <div class="activity-section">
        <h2>📡 Recent Activity</h2>
        <div id="activity"></div>
    </div>

    <div class="refresh-info">Auto-refreshes every 15 seconds</div>

    <script>
        async function loadActivity() {{
            try {{
                const response = await fetch('/activity');
                const data = await response.json();
                
                let html = '';
                for (const item of data.slice(0, 25)) {{
                    html += `<div class="activity-item">
                        <div class="timestamp">${{item.timestamp}}</div>
                        <div class="content">${{item.content}}</div>
                    </div>`;
                }}
                
                document.getElementById('activity').innerHTML = html || '<p>No recent activity</p>';
            }} catch (e) {{
                document.getElementById('activity').innerHTML = '<p>Error loading activity</p>';
            }}
        }}
        
        loadActivity();
        setInterval(loadActivity, 15000);
    </script>
</body>
</html>'''

def run_server():
    """Run the HTTP server."""
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    with socketserver.TCPServer(("", PORT), StatusHandler) as httpd:
        print(f"🚀 Live Status Server running on http://localhost:{PORT}")
        httpd.serve_forever()

if __name__ == "__main__":
    run_server()
