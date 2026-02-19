<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - SDO ALPAS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: #ef4444;
            line-height: 1;
        }
        h1 {
            font-size: 1.5rem;
            color: #1e293b;
            margin: 20px 0 10px;
        }
        p {
            color: #64748b;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #0f4c75;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn:hover {
            background: #1b6ca8;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page. Please contact the administrator if you believe this is an error.</p>
        <a href="/SDO-alpas/admin/" class="btn">
            <i class="fas fa-home"></i> Go to Dashboard
        </a>
    </div>
</body>
</html>
