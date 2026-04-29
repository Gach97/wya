<?php
// Database Status & Test Page
session_start();

$db_host = getenv('DB_HOST') ?: 'mysql';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root';
$db_name = getenv('DB_NAME') ?: 'dlp_db';

$db_status = 'unknown';
$db_message = '';
$tables_exist = [];
$default_user = false;

// Test connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    $db_status = 'error';
    $db_message = htmlspecialchars($conn->connect_error);
} else {
    $db_status = 'connected';
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables_exist[] = $row[0];
    }
    
    // Check default user
    $user_check = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    if ($user_check && $user_check->num_rows > 0) {
        $default_user = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DLP System - Setup Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .status-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .status-section {
            margin-bottom: 25px;
        }

        .status-item {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .status-item.success {
            background: #d3f9d8;
            color: #2f9e44;
        }

        .status-item.error {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .status-item.warning {
            background: #fff3bf;
            color: #f59f00;
        }

        .status-icon {
            font-size: 18px;
            font-weight: bold;
        }

        .status-text {
            flex: 1;
        }

        .status-text strong {
            display: block;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .status-text .detail {
            font-size: 12px;
            opacity: 0.9;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0f0f0;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .table-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            font-size: 12px;
            text-align: center;
        }

        .table-item.exists {
            background: #d3f9d8;
            color: #2f9e44;
        }

        .table-item.missing {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-primary {
            background: #7a0010;
            color: white;
        }

        .btn-primary:hover {
            background: #5b000b;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .summary {
            padding: 15px;
            background: #f5f6fa;
            border-radius: 8px;
            margin-top: 25px;
            text-align: center;
        }

        .summary p {
            margin: 8px 0;
            font-size: 14px;
            color: #333;
        }

        .summary .ready {
            color: #2f9e44;
            font-weight: 600;
        }

        .summary .notready {
            color: #c92a2a;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="status-container">
    <div class="header">
        <h1>🛡️ DLP System Setup Status</h1>
        <p>Database Initialization & Configuration Check</p>
    </div>

    <!-- Database Connection Status -->
    <div class="status-section">
        <div class="section-title">Database Connection</div>
        
        <div class="status-item <?php echo $db_status === 'connected' ? 'success' : 'error'; ?>">
            <div class="status-icon"><?php echo $db_status === 'connected' ? '✓' : '✗'; ?></div>
            <div class="status-text">
                <strong>MySQL Connection</strong>
                <div class="detail">
                    <?php if ($db_status === 'connected'): ?>
                        Connected to <strong><?php echo htmlspecialchars($db_name); ?></strong> on <strong><?php echo htmlspecialchars($db_host); ?></strong>
                    <?php else: ?>
                        Error: <?php echo $db_message; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Tables -->
    <?php if ($db_status === 'connected' && count($tables_exist) > 0): ?>
    <div class="status-section">
        <div class="section-title">Database Tables</div>
        
        <div class="tables-grid">
            <?php 
            $required_tables = ['buckets', 'objects', 'audit_logs', 'users', 'system_settings'];
            foreach ($required_tables as $table): 
                $exists = in_array($table, $tables_exist);
                $conn->select_db($db_name);
                $count_result = $exists ? $conn->query("SELECT COUNT(*) as cnt FROM $table") : null;
                $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;
            ?>
                <div class="table-item <?php echo $exists ? 'exists' : 'missing'; ?>">
                    <div><?php echo $exists ? '✓' : '✗'; ?> <?php echo htmlspecialchars($table); ?></div>
                    <?php if ($exists): ?>
                        <div style="font-size: 11px; margin-top: 4px; opacity: 0.9;"><?php echo $count; ?> records</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Default User -->
    <?php if ($db_status === 'connected'): ?>
    <div class="status-section">
        <div class="section-title">Default Credentials</div>
        
        <div class="status-item <?php echo $default_user ? 'success' : 'error'; ?>">
            <div class="status-icon"><?php echo $default_user ? '✓' : '✗'; ?></div>
            <div class="status-text">
                <strong>Admin User</strong>
                <div class="detail">
                    <?php if ($default_user): ?>
                        Default admin user exists<br>
                        <strong>Username:</strong> admin<br>
                        <strong>Password:</strong> (hashed in DB)
                    <?php else: ?>
                        Admin user not found - database may not be initialized
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="summary">
        <?php 
        $ready = ($db_status === 'connected' && count($tables_exist) === 5 && $default_user);
        ?>
        <?php if ($ready): ?>
            <p class="ready">✅ Database Setup Complete!</p>
            <p>The database has been initialized with all tables and default data.</p>
        <?php else: ?>
            <p class="notready">⚠️ Database Setup Incomplete</p>
            <p>Some components are missing. The Docker container may still be initializing.</p>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn btn-primary" onclick="location.reload()">🔄 Refresh Status</button>
        <a href="login.php" class="btn btn-secondary">📱 Go to Login</a>
    </div>
</div>

</body>
</html>
