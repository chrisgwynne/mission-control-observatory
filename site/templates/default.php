<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page->title() ?> | Mission Control</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'SF Mono', Monaco, 'Fira Code', monospace;
            background: #050508;
            color: #a0a0b0;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        h1 { color: #fff; font-size: 2rem; margin-bottom: 1rem; }
        .content { margin: 1.5rem 0; text-align: left; }
        .content h2 { color: #fff; margin: 1rem 0 0.5rem; font-size: 1.25rem; }
        .content p { margin-bottom: 1rem; }
        .nav { margin-top: 2rem; }
        a {
            color: #3b82f6;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            display: inline-block;
            margin: 0 0.5rem;
            transition: all 0.2s;
        }
        a:hover {
            background: #3b82f6;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $page->title() ?></h1>
        <div class="content">
            <?= $page->text()->kirbytext() ?>
        </div>
        <div class="nav">
            <a href="/">← Home</a>
            <a href="/agents">Agents →</a>
        </div>
    </div>
</body>
</html>
