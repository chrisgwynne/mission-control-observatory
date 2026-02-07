<?php
$workspaceLog = '/home/chris/.openclaw/workspace/mission-control/logs/activity.md';
$content = file_get_contents($workspaceLog);

// Find all timestamps
preg_match_all('/## (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $content, $matches);

echo "Found " . count($matches[1]) . " entries<br>";
echo "Latest 5 timestamps:<br>";
foreach (array_slice($matches[1], 0, 5) as $ts) {
    echo "- $ts<br>";
}
