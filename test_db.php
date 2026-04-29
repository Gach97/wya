<?php
// Test database connection and schema

error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'mysql';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'dlp_db';

echo "🔍 Testing Database Connection...\n";
echo "================================\n\n";

// Try to connect
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error . "\n";
    exit(1);
}

echo "✅ Connected to MySQL successfully!\n";
echo "   Host: $db_host\n";
echo "   Database: $db_name\n\n";

// Check tables
echo "📋 Checking Database Tables...\n";
echo "------------------------------\n";

$tables = ['buckets', 'objects', 'audit_logs', 'users', 'system_settings'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Table '$table' exists\n";
        
        // Count records
        $count_result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
        $count_row = $count_result->fetch_assoc();
        echo "   Records: " . $count_row['cnt'] . "\n";
    } else {
        echo "❌ Table '$table' NOT found\n";
    }
}

echo "\n🔐 Users Table:\n";
echo "---------------\n";
$result = $conn->query("SELECT id, username, email, role FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   ID: {$row['id']} | Username: {$row['username']} | Role: {$row['role']}\n";
    }
}

echo "\n🪣 Buckets Table:\n";
echo "-----------------\n";
$result = $conn->query("SELECT id, name, description FROM buckets");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   ID: {$row['id']} | Name: {$row['name']} | Desc: {$row['description']}\n";
    }
}

echo "\n⚙️  System Settings (Sample):\n";
echo "----------------------------\n";
$result = $conn->query("SELECT setting_key, value FROM system_settings LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['setting_key']}: {$row['value']}\n";
    }
}

echo "\n✨ Database setup verification complete!\n";
$conn->close();
?>
