<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Classification - DLP System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", "Segoe UI", sans-serif;
        }

        body {
            background: #f5f6fa;
            display: flex;
        }

        .main {
            margin-left: 280px;
            padding: 20px;
            width: calc(100% - 280px);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0 25px 0;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
        }

        .top-bar h2 {
            margin: 0;
            color: #1a1a1a;
            font-size: 24px;
        }

        .top-bar p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        /* Classification Engine Section */
        .classification-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.08);
        }

        .scan-textarea {
            width: 100%;
            height: 250px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: "Poppins", sans-serif;
            font-size: 13px;
            resize: none;
            transition: border-color 0.3s ease;
        }

        .scan-textarea:focus {
            outline: none;
            border-color: #7a0010;
        }

        .textarea-controls {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #7a0010;
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            background: #5b000b;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-action {
            padding: 10px 16px;
            font-size: 12px;
        }

        .result-container {
            margin-top: 20px;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
            display: none;
        }

        .result-container.show {
            display: block;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .classification-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .badge-public {
            background: #d3f9d8;
            color: #2f9e44;
        }

        .badge-internal {
            background: #d0ebff;
            color: #1971c2;
        }

        .badge-confidential {
            background: #fff3bf;
            color: #f59f00;
        }

        .badge-restricted {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .scanned-text {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            line-height: 1.6;
            font-size: 13px;
            color: #333;
            max-height: 200px;
            overflow-y: auto;
        }

        .keyword-match {
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }

        .keyword-public {
            background: #d3f9d8;
            color: #2f9e44;
        }

        .keyword-internal {
            background: #d0ebff;
            color: #1971c2;
        }

        .keyword-confidential {
            background: #fff3bf;
            color: #f59f00;
        }

        .keyword-restricted {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .triggered-keywords {
            margin-top: 15px;
        }

        .triggered-keywords h5 {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .keyword-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .keyword-tag {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .keyword-tag-public {
            background: #e7f5e9;
            color: #2f9e44;
        }

        .keyword-tag-internal {
            background: #e7f0f9;
            color: #1971c2;
        }

        .keyword-tag-confidential {
            background: #fffbe6;
            color: #f59f00;
        }

        .keyword-tag-restricted {
            background: #fef0f0;
            color: #c92a2a;
        }

        .action-buttons {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-action {
            padding: 10px;
            font-size: 12px;
        }

        .btn-email {
            background: #4dabf7;
            color: white;
        }

        .btn-email:hover {
            background: #1971c2;
        }

        .btn-usb {
            background: #ff922b;
            color: white;
        }

        .btn-usb:hover {
            background: #f76707;
        }

        .btn-cloud {
            background: #51cf66;
            color: white;
        }

        .btn-cloud:hover {
            background: #2f9e44;
        }

        .btn-share {
            background: #a78bfa;
            color: white;
        }

        .btn-share:hover {
            background: #7c3aed;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0px 8px 24px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #1a1a1a;
        }

        .modal-header.warn h3 {
            color: #f59f00;
        }

        .modal-header.block h3 {
            color: #c92a2a;
        }

        .modal-body {
            margin-bottom: 25px;
            line-height: 1.6;
            color: #333;
            font-size: 14px;
        }

        .modal-body strong {
            color: #1a1a1a;
        }

        .modal-body p {
            margin: 10px 0;
        }

        .legal-reference {
            background: #fafafa;
            padding: 12px;
            border-left: 4px solid #7a0010;
            margin: 15px 0;
            font-size: 12px;
            color: #666;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel, .btn-confirm {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #e0e0e0;
        }

        .btn-confirm {
            background: #7a0010;
            color: white;
        }

        .btn-confirm:hover {
            background: #5b000b;
        }

        .notice {
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-top: 10px;
            animation: slideInUp 0.3s ease;
        }

        .notice-success {
            background: #d3f9d8;
            color: #2f9e44;
        }

        .notice-error {
            background: #ffe3e3;
            color: #c92a2a;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dashboard Section */
        .dashboard-section {
            grid-column: 1 / -1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #7a0010;
        }

        .stat-card h4 {
            margin: 0;
            color: #666;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            margin: 10px 0 0 0;
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .stat-card .subtext {
            margin: 5px 0 0 0;
            color: #999;
            font-size: 12px;
        }

        .stat-card.threat {
            border-left-color: #ff6b6b;
        }

        .stat-card.blocked {
            border-left-color: #c92a2a;
        }

        .stat-card.score {
            border-left-color: #51cf66;
        }

        /* Activity Log */
        .activity-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .activity-section h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            text-align: left;
        }

        th {
            background: #fafafa;
            color: #666;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            color: #333;
            font-size: 13px;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        tbody tr.new-entry {
            animation: highlightRow 0.5s ease;
        }

        @keyframes highlightRow {
            from {
                background: #d3f9d8;
            }
            to {
                background: transparent;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-allowed {
            background: #d3f9d8;
            color: #2f9e44;
        }

        .status-warned {
            background: #fff3bf;
            color: #f59f00;
        }

        .status-blocked {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .status-cancelled {
            background: #f0f0f0;
            color: #666;
        }

        /* Chart */
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.08);
            position: relative;
            height: 400px;
        }

        .chart-container h3 {
            margin: 0 0 20px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
        }

        #classificationChart {
            max-height: 300px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .main {
                margin-left: 100px;
                width: calc(100% - 100px);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="top-bar">
        <div>
            <h2>Data Classification</h2>
            <p>Scan & Classify Sensitive Content | Policy Enforcement & Monitoring</p>
        </div>
        <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
    </div>

    <div class="container">

        <!-- CLASSIFICATION ENGINE SECTION -->
        <div class="classification-box">
            <h3 class="section-title">📋 Classification Engine</h3>
            
            <label for="content-input" style="font-size: 12px; color: #666; font-weight: 500; margin-bottom: 8px; display: block;">Paste text or file content to scan:</label>
            <textarea id="content-input" class="scan-textarea" placeholder="Paste any text, email content, or simulated file data here...&#10;&#10;Example: 'Patient John Doe diagnosed with HIV, salary information: $50,000, credit card 4532-1234-5678-9012'"></textarea>

            <div class="textarea-controls">
                <button class="btn btn-primary" onclick="scanContent()">🔍 SCAN</button>
                <button class="btn btn-secondary" onclick="clearContent()">Clear</button>
            </div>

            <!-- Result Container -->
            <div class="result-container" id="result-container">
                <div class="classification-badge" id="classification-badge"></div>
                
                <div class="scanned-text" id="scanned-text"></div>

                <div class="triggered-keywords">
                    <h5>Matched Keywords</h5>
                    <div class="keyword-list" id="keyword-list"></div>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-action btn-email" onclick="triggerAction('email')">📧 Email</button>
                    <button class="btn btn-action btn-usb" onclick="triggerAction('usb')">💾 Copy to USB</button>
                    <button class="btn btn-action btn-cloud" onclick="triggerAction('cloud')">☁️ Upload to Cloud</button>
                    <button class="btn btn-action btn-share" onclick="triggerAction('share')">🔗 Share Internally</button>
                </div>

                <div id="action-notice"></div>
            </div>
        </div>

        <!-- POLICY ENFORCEMENT REFERENCE SECTION -->
        <div class="classification-box">
            <h3 class="section-title">📜 Policy Enforcement Matrix</h3>
            
            <table style="font-size: 12px; margin-bottom: 15px;">
                <thead>
                    <tr style="background: #fafafa;">
                        <th>Classification</th>
                        <th>Email</th>
                        <th>USB</th>
                        <th>Cloud</th>
                        <th>Internal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Public</strong></td>
                        <td style="color: #2f9e44;">✓ Allow</td>
                        <td style="color: #2f9e44;">✓ Allow</td>
                        <td style="color: #2f9e44;">✓ Allow</td>
                        <td style="color: #2f9e44;">✓ Allow</td>
                    </tr>
                    <tr>
                        <td><strong>Internal</strong></td>
                        <td style="color: #f59f00;">⚠ Warn</td>
                        <td style="color: #f59f00;">⚠ Warn</td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #2f9e44;">✓ Allow</td>
                    </tr>
                    <tr>
                        <td><strong>Confidential</strong></td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #f59f00;">⚠ Warn</td>
                    </tr>
                    <tr>
                        <td><strong>Restricted</strong></td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #c92a2a;">✗ Block</td>
                        <td style="color: #c92a2a;">✗ Block</td>
                    </tr>
                </tbody>
            </table>

            <div style="background: #fafafa; padding: 12px; border-radius: 6px; font-size: 12px; color: #666; line-height: 1.6;">
                <p><strong>Category Definitions:</strong></p>
                <p>🔴 <strong>Restricted:</strong> Payroll, salary, bank accounts, patient records, diagnosis, tax PIN, national ID</p>
                <p>🟡 <strong>Confidential:</strong> Internal use only, do not share, private, personnel, performance review</p>
                <p>🔵 <strong>Internal:</strong> Internal, staff only, draft, not for public, memo</p>
                <p>🟢 <strong>Public:</strong> All other content</p>
            </div>
        </div>

    </div>

    <!-- ADMIN DASHBOARD SECTION -->
    <div class="dashboard-section">
        <div class="stats-grid">
            <div class="stat-card">
                <h4>📊 Total Scans</h4>
                <div class="value" id="total-scans">0</div>
                <div class="subtext">during this session</div>
            </div>

            <div class="stat-card threat">
                <h4>⚠️ Threats Detected</h4>
                <div class="value" id="threats-count">0</div>
                <div class="subtext">confidential + restricted</div>
            </div>

            <div class="stat-card blocked">
                <h4>🚫 Transfers Blocked</h4>
                <div class="value" id="blocked-count">0</div>
                <div class="subtext">blocked actions</div>
            </div>

            <div class="stat-card score">
                <h4>✅ Compliance Score</h4>
                <div class="value"><span id="compliance-score">100</span>%</div>
                <div class="subtext">allowed / total actions</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
            <div class="activity-section">
                <h3>📋 Activity Log</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Content</th>
                            <th>Classification</th>
                            <th>Action</th>
                            <th>Outcome</th>
                        </tr>
                    </thead>
                    <tbody id="activity-log">
                        <!-- Populated with dummy data and live updates -->
                    </tbody>
                </table>
            </div>

            <div class="chart-container">
                <h3>📈 Classification Distribution</h3>
                <canvas id="classificationChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- MODALS -->

<!-- Warn Modal -->
<div class="modal" id="warn-modal">
    <div class="modal-content">
        <div class="modal-header warn">
            <h3>⚠️ Action Requires Confirmation</h3>
            <p style="margin: 0; font-size: 13px; color: #666;">This action is permitted but requires review.</p>
        </div>
        <div class="modal-body">
            <p>You are attempting to <strong id="warn-action-text">send this content via email</strong>. This content is classified as <strong id="warn-classification">INTERNAL</strong> and may contain sensitive information.</p>
            
            <div class="legal-reference">
                <strong>Kenya Data Protection Act (2019)</strong><br>
                Section 46 & 48: Organizations must implement appropriate security measures and ensure lawful processing of personal data. Unauthorized disclosure of protected information may result in penalties.
            </div>

            <p>Do you want to proceed with this action? It will be logged as <em>"Allowed with warning"</em> for audit purposes.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cancelAction()">Cancel</button>
            <button class="btn-confirm" onclick="confirmAction()">Confirm & Proceed</button>
        </div>
    </div>
</div>

<!-- Block Modal -->
<div class="modal" id="block-modal">
    <div class="modal-content">
        <div class="modal-header block">
            <h3>🚫 Action Blocked</h3>
            <p style="margin: 0; font-size: 13px; color: #666;">This action cannot be performed.</p>
        </div>
        <div class="modal-body">
            <p>Your attempt to <strong id="block-action-text">send this content via email</strong> has been <strong style="color: #c92a2a;">blocked by policy</strong>.</p>

            <p><strong>Reason:</strong> This content is classified as <strong id="block-classification">RESTRICTED</strong>, which contains highly sensitive information and cannot be transferred through this channel.</p>
            
            <div class="legal-reference">
                <strong>Kenya Data Protection Act (2019)</strong><br>
                Section 46: Personal data must not be transferred without appropriate safeguards. Data controllers must implement technical and organizational measures to ensure the security of personal data during any transfer.
            </div>

            <p style="color: #c92a2a;"><strong>⛔ This transfer has been stopped. No further action is possible.</strong></p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeBlockModal()" style="margin-left: auto;">OK, Close</button>
        </div>
    </div>
</div>

<script>
    // Keyword Lists
    const keywords = {
        restricted: ['payroll', 'salary', 'bank account', 'account number', 'patient', 'diagnosis', 'hiv', 'tax pin', 'national id', 'pin number', 'credit card'],
        confidential: ['confidential', 'internal use only', 'do not share', 'private', 'personnel', 'performance review'],
        internal: ['internal', 'staff only', 'draft', 'not for public', 'memo'],
    };

    const simulated_users = ['Alice Mwangi', 'Brian Otieno', 'Carol Njeri', 'David Kamau'];
    let activity_log = [];
    let current_classification = null;
    let current_matched_keywords = [];
    let current_action = null;
    let chart_instance = null;

    // Initialize dashboard with dummy data
    function initializeDashboard() {
        // Create 5-6 realistic dummy entries
        const dummy_entries = [
            {
                time: new Date(Date.now() - 25 * 60000),
                user: 'Alice Mwangi',
                content: 'Patient record containing diagnosis',
                classification: 'RESTRICTED',
                action: 'Upload to Cloud',
                outcome: 'BLOCKED'
            },
            {
                time: new Date(Date.now() - 20 * 60000),
                user: 'Brian Otieno',
                content: 'Internal memo regarding staff meeting',
                classification: 'INTERNAL',
                action: 'Share Internally',
                outcome: 'ALLOWED'
            },
            {
                time: new Date(Date.now() - 15 * 60000),
                user: 'Carol Njeri',
                content: 'Confidential performance review document',
                classification: 'CONFIDENTIAL',
                action: 'Email',
                outcome: 'BLOCKED'
            },
            {
                time: new Date(Date.now() - 10 * 60000),
                user: 'David Kamau',
                content: 'Public announcement bulletin',
                classification: 'PUBLIC',
                action: 'Email',
                outcome: 'ALLOWED'
            },
            {
                time: new Date(Date.now() - 5 * 60000),
                user: 'Alice Mwangi',
                content: 'Confidential salary information',
                classification: 'CONFIDENTIAL',
                action: 'Copy to USB',
                outcome: 'ALLOWED WITH WARNING'
            },
            {
                time: new Date(Date.now() - 2 * 60000),
                user: 'Brian Otieno',
                content: 'Regular business document',
                classification: 'PUBLIC',
                action: 'Upload to Cloud',
                outcome: 'ALLOWED'
            }
        ];

        activity_log = dummy_entries;
        renderActivityLog();
        updateMetrics();
        initializeChart();
    }

    function scanContent() {
        const content = document.getElementById('content-input').value.trim();
        
        if (!content) {
            alert('Please paste some content to scan.');
            return;
        }

        // Find matched keywords (highest category priority)
        const matches = {
            restricted: [],
            confidential: [],
            internal: [],
            public: []
        };

        const content_lower = content.toLowerCase();

        // Check for matches in each category
        keywords.restricted.forEach(keyword => {
            if (content_lower.includes(keyword.toLowerCase())) {
                matches.restricted.push(keyword);
            }
        });

        keywords.confidential.forEach(keyword => {
            if (content_lower.includes(keyword.toLowerCase())) {
                matches.confidential.push(keyword);
            }
        });

        keywords.internal.forEach(keyword => {
            if (content_lower.includes(keyword.toLowerCase())) {
                matches.internal.push(keyword);
            }
        });

        // Determine highest category
        let classification = 'PUBLIC';
        let matched_keywords = [];

        if (matches.restricted.length > 0) {
            classification = 'RESTRICTED';
            matched_keywords = matches.restricted;
        } else if (matches.confidential.length > 0) {
            classification = 'CONFIDENTIAL';
            matched_keywords = matches.confidential;
        } else if (matches.internal.length > 0) {
            classification = 'INTERNAL';
            matched_keywords = matches.internal;
        }

        current_classification = classification;
        current_matched_keywords = matched_keywords;

        // Display result
        displayResult(content, classification, matched_keywords);
    }

    function displayResult(content, classification, matched_keywords) {
        // Show result container
        const result_container = document.getElementById('result-container');
        result_container.classList.add('show');

        // Set badge
        const badge = document.getElementById('classification-badge');
        badge.textContent = '📌 ' + classification;
        badge.className = 'classification-badge badge-' + classification.toLowerCase();

        // Highlight keywords in text
        let highlighted_text = content;
        const unique_keywords = [...new Set(matched_keywords)];

        unique_keywords.forEach(keyword => {
            const regex = new RegExp('\\b' + keyword + '\\b', 'gi');
            const badge_class = 'keyword-' + classification.toLowerCase();
            highlighted_text = highlighted_text.replace(regex, `<span class="keyword-match ${badge_class}">${keyword}</span>`);
        });

        document.getElementById('scanned-text').innerHTML = highlighted_text;

        // Display matched keywords
        const keyword_list = document.getElementById('keyword-list');
        keyword_list.innerHTML = '';
        unique_keywords.forEach(keyword => {
            const tag = document.createElement('span');
            tag.className = 'keyword-tag keyword-tag-' + classification.toLowerCase();
            tag.textContent = keyword;
            keyword_list.appendChild(tag);
        });

        // Hide action buttons if no threats
        const action_buttons = document.querySelector('.action-buttons');
        if (classification === 'PUBLIC') {
            action_buttons.style.display = 'grid';
        }
    }

    function triggerAction(action_type) {
        current_action = action_type;

        const action_names = {
            'email': 'send via Email',
            'usb': 'copy to USB',
            'cloud': 'upload to Cloud',
            'share': 'share Internally'
        };

        // Policy matrix
        const policy = {
            'PUBLIC': { email: 'allow', usb: 'allow', cloud: 'allow', share: 'allow' },
            'INTERNAL': { email: 'warn', usb: 'warn', cloud: 'block', share: 'allow' },
            'CONFIDENTIAL': { email: 'block', usb: 'block', cloud: 'block', share: 'warn' },
            'RESTRICTED': { email: 'block', usb: 'block', cloud: 'block', share: 'block' }
        };

        const action_policy = policy[current_classification][action_type];

        if (action_policy === 'allow') {
            logAction(action_type, 'ALLOWED');
            showNotice('success', `✓ Content transfer via ${action_names[action_type]} has been allowed and logged.`);
        } else if (action_policy === 'warn') {
            document.getElementById('warn-action-text').textContent = action_names[action_type];
            document.getElementById('warn-classification').textContent = current_classification;
            document.getElementById('warn-modal').classList.add('show');
        } else if (action_policy === 'block') {
            document.getElementById('block-action-text').textContent = action_names[action_type];
            document.getElementById('block-classification').textContent = current_classification;
            document.getElementById('block-modal').classList.add('show');
            logAction(action_type, 'BLOCKED');
        }
    }

    function confirmAction() {
        closeWarnModal();
        logAction(current_action, 'ALLOWED WITH WARNING');
        showNotice('success', `✓ Action confirmed. Content transfer has been logged as "Allowed with warning".`);
    }

    function cancelAction() {
        closeWarnModal();
        logAction(current_action, 'CANCELLED');
        showNotice('success', `⊘ Action cancelled and logged.`);
    }

    function closeWarnModal() {
        document.getElementById('warn-modal').classList.remove('show');
    }

    function closeBlockModal() {
        document.getElementById('block-modal').classList.remove('show');
    }

    function showNotice(type, message) {
        const notice_element = document.getElementById('action-notice');
        notice_element.className = 'notice notice-' + type;
        notice_element.textContent = message;
        notice_element.style.display = 'block';
        setTimeout(() => {
            notice_element.style.display = 'none';
        }, 4000);
    }

    function logAction(action_type, outcome) {
        const action_names = {
            'email': 'Email',
            'usb': 'Copy to USB',
            'cloud': 'Upload to Cloud',
            'share': 'Share Internally'
        };

        const entry = {
            time: new Date(),
            user: simulated_users[Math.floor(Math.random() * simulated_users.length)],
            content: document.getElementById('content-input').value.substring(0, 40),
            classification: current_classification,
            action: action_names[action_type],
            outcome: outcome
        };

        activity_log.unshift(entry);
        renderActivityLog();
        updateMetrics();
    }

    function renderActivityLog() {
        const log_body = document.getElementById('activity-log');
        log_body.innerHTML = '';

        activity_log.forEach((entry, index) => {
            const row = document.createElement('tr');
            if (index === 0 && activity_log.length > 6) {
                row.classList.add('new-entry');
            }

            const outcome_status = entry.outcome.includes('ALLOWED') ? 'allowed' :
                                   entry.outcome === 'BLOCKED' ? 'blocked' :
                                   entry.outcome === 'CANCELLED' ? 'cancelled' : 'warned';

            const classification_badge = 'badge-' + entry.classification.toLowerCase();
            
            row.innerHTML = `
                <td>${entry.time.toLocaleTimeString()}</td>
                <td>${entry.user}</td>
                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${entry.content || 'N/A'}</td>
                <td><span class="status-badge ${classification_badge}" style="background: var(--badge-bg);">${entry.classification}</span></td>
                <td>${entry.action}</td>
                <td><span class="status-badge status-${outcome_status}">${entry.outcome}</span></td>
            `;

            // Set background color based on classification
            if (entry.classification === 'RESTRICTED') {
                row.querySelector('td:nth-child(4) span').style.background = '#ffe3e3';
                row.querySelector('td:nth-child(4) span').style.color = '#c92a2a';
            } else if (entry.classification === 'CONFIDENTIAL') {
                row.querySelector('td:nth-child(4) span').style.background = '#fff3bf';
                row.querySelector('td:nth-child(4) span').style.color = '#f59f00';
            } else if (entry.classification === 'INTERNAL') {
                row.querySelector('td:nth-child(4) span').style.background = '#d0ebff';
                row.querySelector('td:nth-child(4) span').style.color = '#1971c2';
            } else {
                row.querySelector('td:nth-child(4) span').style.background = '#d3f9d8';
                row.querySelector('td:nth-child(4) span').style.color = '#2f9e44';
            }

            log_body.appendChild(row);
        });
    }

    function updateMetrics() {
        // Total scans
        const scans = activity_log.length;
        document.getElementById('total-scans').textContent = scans;

        // Threats detected (Confidential + Restricted)
        const threats = activity_log.filter(e => e.classification === 'CONFIDENTIAL' || e.classification === 'RESTRICTED').length;
        document.getElementById('threats-count').textContent = threats;

        // Transfers blocked
        const blocked = activity_log.filter(e => e.outcome === 'BLOCKED').length;
        document.getElementById('blocked-count').textContent = blocked;

        // Compliance score
        const allowed = activity_log.filter(e => e.outcome.includes('ALLOWED')).length;
        const total_actions = scans;
        const compliance = total_actions > 0 ? Math.round((allowed / total_actions) * 100) : 100;
        document.getElementById('compliance-score').textContent = compliance;

        // Update chart
        updateChart();
    }

    function initializeChart() {
        const ctx = document.getElementById('classificationChart').getContext('2d');
        
        const classification_counts = {
            'PUBLIC': 0,
            'INTERNAL': 0,
            'CONFIDENTIAL': 0,
            'RESTRICTED': 0
        };

        activity_log.forEach(entry => {
            classification_counts[entry.classification]++;
        });

        chart_instance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Public', 'Internal', 'Confidential', 'Restricted'],
                datasets: [{
                    data: [
                        classification_counts['PUBLIC'],
                        classification_counts['INTERNAL'],
                        classification_counts['CONFIDENTIAL'],
                        classification_counts['RESTRICTED']
                    ],
                    backgroundColor: [
                        '#51cf66',  // Public - Green
                        '#4dabf7',  // Internal - Blue
                        '#ffd43b',  // Confidential - Yellow
                        '#ff6b6b'   // Restricted - Red
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 },
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    function updateChart() {
        if (!chart_instance) return;

        const classification_counts = {
            'PUBLIC': 0,
            'INTERNAL': 0,
            'CONFIDENTIAL': 0,
            'RESTRICTED': 0
        };

        activity_log.forEach(entry => {
            classification_counts[entry.classification]++;
        });

        chart_instance.data.datasets[0].data = [
            classification_counts['PUBLIC'],
            classification_counts['INTERNAL'],
            classification_counts['CONFIDENTIAL'],
            classification_counts['RESTRICTED']
        ];
        chart_instance.update();
    }

    function clearContent() {
        document.getElementById('content-input').value = '';
        document.getElementById('result-container').classList.remove('show');
        document.getElementById('action-notice').innerHTML = '';
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
    });
</script>

</body>
</html>
