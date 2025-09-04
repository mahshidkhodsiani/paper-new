<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paperet Competition Hosting</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand: #0ea5b3;
            --brand-dark: #0c8a96;
            --brand-light: #ecfeff;
            --text: #0f172a;
            --text-light: #64748b;
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.2s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
            max-width: 1800px;
            margin: 0 auto;
        }

        /* Header */
        .app-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .app-header img {
            height: 48px;
            border-radius: var(--radius-sm);
            object-fit: contain;
        }

        .app-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        /* Cards */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-light);
        }

        /* Forms */
        fieldset {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            background: var(--surface);
        }

        legend {
            font-weight: 600;
            padding: 0 12px;
            color: var(--text);
            font-size: 1.125rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--surface);
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(14, 165, 179, 0.15);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-row {
            display: grid;
            gap: 16px;
            margin-bottom: 16px;
        }

        @media (min-width: 640px) {
            .form-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background: var(--brand);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            gap: 8px;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border-light);
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 0.875rem;
        }

        /* Pills & Chips */
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background: var(--brand-light);
            color: var(--brand);
            border-radius: 100px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-right: 8px;
            margin-bottom: 8px;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .pill:hover {
            background: var(--brand);
            color: white;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        th,
        td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: var(--border-light);
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Utilities */
        .text-muted {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .text-success {
            color: var(--success);
        }

        .text-warning {
            color: var(--warning);
        }

        .text-error {
            color: var(--error);
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .d-flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .align-center {
            align-items: center;
        }

        .gap-2 {
            gap: 8px;
        }

        .gap-4 {
            gap: 16px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .badge-info {
            background: rgba(14, 165, 179, 0.1);
            color: var(--brand);
        }

        /* Toast notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-success {
            border-left: 4px solid var(--success);
        }

        .toast-error {
            border-left: 4px solid var(--error);
        }

        /* Loading indicator */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid var(--brand);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .tab {
            padding: 12px 20px;
            font-weight: 500;
            color: var(--text-light);
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .tab.active {
            color: var(--brand);
            border-bottom-color: var(--brand);
        }

        .tab:hover {
            color: var(--text);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .app-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 16px;
            }

            fieldset {
                padding: 16px;
            }

            .tabs {
                overflow-x: auto;
            }
        }

        /* Layout */
        .container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }

        @media (min-width: 1024px) {
            .container {
                grid-template-columns: 2fr 1fr;
            }
        }

        /* New Styles for Enhanced UI */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--brand);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .progress-bar {
            height: 8px;
            background: var(--border-light);
            border-radius: 4px;
            overflow: hidden;
            margin: 16px 0;
        }

        .progress-fill {
            height: 100%;
            background: var(--brand);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 24px 0;
        }

        .feature-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            font-size: 2rem;
            color: var(--brand);
            margin-bottom: 16px;
        }

        .demo-bar {
            background: var(--brand-light);
            border-radius: var(--radius);
            padding: 16px;
            margin: 20px 0;
            border-left: 4px solid var(--brand);
        }

        .tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted var(--text-light);
            cursor: help;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: var(--text);
            color: var(--surface);
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.875rem;
            font-weight: normal;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 32px 0 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border);
            color: var(--text);
        }

        /* Form validation */
        .error-message {
            color: var(--error);
            font-size: 0.875rem;
            margin-top: 4px;
            display: none;
        }

        input.error,
        textarea.error,
        select.error {
            border-color: var(--error);
        }

        /* Competition view/edit modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 24px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        /* Tag input */
        .tag-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
        }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            background: var(--brand-light);
            color: var(--brand);
            border-radius: 100px;
            font-size: 0.875rem;
        }

        .tag-remove {
            margin-left: 4px;
            cursor: pointer;
        }

        .tag-input {
            flex: 1;
            min-width: 60px;
            border: none;
            outline: none;
            padding: 0;
        }

        /* === Aligned options styling (added) === */
        .align-options>div,
        .checkbox-item,
        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .align-options input[type="checkbox"],
        .align-options input[type="radio"],
        .checkbox-item input[type="checkbox"],
        .radio-item input[type="radio"] {
            margin: 0;
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }

        .align-options label,
        .checkbox-item label,
        .radio-item label {
            margin: 0;
        }

        /* Make groups tidy on narrow screens */
        .option-list {
            display: grid;
            gap: 8px;
        }


        /* === Options grid + switch rows (added) === */
        .options-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .options-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .switch-row {
            display: grid;
            grid-template-columns: 20px 1fr;
            align-items: start;
            column-gap: 10px;
        }

        .switch-row input[type="checkbox"] {
            margin-top: 4px;
        }

        .switch-row .hint {
            display: block;
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <!-- Toast container -->
    <div id="toastContainer"></div>

    <!-- Competition View/Edit Modal -->
    <div class="modal" id="competitionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Competition Details</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="app-header">
        <div style="width: 48px; height: 48px; background: var(--brand); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">P</div>
        <h1 class="app-title">Paperet Competition Hosting</h1>
    </header>

    <div class="demo-bar">
        <div class="d-flex align-center gap-4">
            <div><i class="fas fa-info-circle"></i></div>
            <div>
                <strong>Demo Mode:</strong> This is a preview of the enhanced Paperet Competition Hosting platform.
                The backend integration would be required for full functionality.
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">4</div>
            <div class="stat-label">Active Competitions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">127</div>
            <div class="stat-label">Total Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">23</div>
            <div class="stat-label">Judges</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">86%</div>
            <div class="stat-label">Completion Rate</div>
        </div>
    </div>

    <div class="container">
        <!-- Left: Setup form -->
        <div>
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="d-flex justify-between align-center">
                    <div>
                        <h3>Create New Competition</h3>
                        <p class="text-muted">Set up a new competition in minutes with our step-by-step wizard</p>
                    </div>
                    <div class="spinner" id="headerSpinner" style="display: none;"></div>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: 14%;"></div>
                </div>

                <div class="d-flex justify-between mt-4">
                    <button type="button" class="btn btn-secondary" id="prevTab">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn" id="nextTab">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn" id="submitBtn" style="display: none;">
                        <span>Create Competition</span>
                        <div class="spinner" id="submitSpinner" style="display: none;"></div>
                    </button>
                </div>
            </div>

            <form class="card" method="post" enctype="multipart/form-data" id="competitionForm">
                <input type="hidden" name="scope" value="setup">
                <input type="hidden" name="action" value="create">

                <div class="tabs" id="formTabs">
                    <div class="tab active" data-tab="basic">Basic Info</div>
                    <div class="tab" data-tab="details">Details</div>
                    <div class="tab" data-tab="privacy">Privacy & Voting</div>
                    <div class="tab" data-tab="people">People</div>
                    <div class="tab" data-tab="submission">Submission</div>
                    <div class="tab" data-tab="awards">Awards</div>
                    <div class="tab" data-tab="advanced">Advanced</div>
                </div>

                <!-- Tab Content -->
                <div id="tabContent">
                    <!-- Basic Info Tab -->
                    <div id="tab-basic" class="tab-pane active">
                        <fieldset>
                            <legend>1. Organizer & Branding</legend>
                            <div class="form-group">
                                <label for="organization">Organization <span class="text-error">*</span></label>
                                <input type="text" id="organization" name="org_name" placeholder="Your organization name" required>
                                <div class="error-message" id="orgError">Please enter an organization name</div>
                                <div class="text-muted">This will be displayed to participants and judges</div>
                            </div>
                            <div class="form-group">
                                <label for="org_email">Organizer Contact Email <span class="text-error">*</span></label>
                                <input type="email" id="org_email" name="org_email" placeholder="organizer@example.com" required>
                                <div class="error-message" id="emailError">Please enter a valid email address</div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="logo">Logo (optional)</label>
                                    <input type="file" id="logo" name="logo" accept="image/*">
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </form>

            <div class="section-title">Key Features</div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Participant Management</h3>
                    <p>Invite participants with personalized tokens for private competitions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h3>Judge Assignments</h3>
                    <p>Manage judges, assign entries, and track scoring progress</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Awards & Certificates</h3>
                    <p>Create custom certificates for winners, participants, and judges</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Live Leaderboard</h3>
                    <p>Real-time scoring with weighted and normalized views</p>
                </div>
            </div>
        </div>

        <!-- Right: Existing competitions -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2>Your Competitions</h2>
                    <span class="badge badge-info" id="competitionCount">4</span>
                </div>

                <div id="competitionsList">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">Spring 2023 Research Symposium</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Apr 15, 2023
                                    </div>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm" onclick="viewCompetition('comp1')">View</button>
                                        <button class="btn btn-sm btn-secondary" onclick="editCompetition('comp1')">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">Summer Innovation Challenge</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Jul 10, 2023
                                    </div>
                                </td>
                                <td><span class="badge badge-warning">Completed</span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm" onclick="viewCompetition('comp2')">View</button>
                                        <button class="btn btn-sm btn-secondary" onclick="editCompetition('comp2')">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">Fall Design Competition</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Oct 5, 2023
                                    </div>
                                </td>
                                <td><span class="badge badge-info">Upcoming</span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm" onclick="viewCompetition('comp3')">View</button>
                                        <button class="btn btn-sm btn-secondary" onclick="editCompetition('comp3')">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Quick Actions</h3>
                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                    <button class="btn btn-secondary btn-sm" onclick="showToast('Export feature coming soon', 'info')">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="showToast('Documentation coming soon', 'info')">
                        <i class="fas fa-book"></i> Documentation
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="showToast('Support coming soon', 'info')">
                        <i class="fas fa-life-ring"></i> Support
                    </button>
                </div>
            </div>

            <div class="card">
                <h3>Getting Started</h3>
                <p class="text-muted">New to Paperet? Follow these steps to set up your first competition:</p>
                <ol style="margin-left: 20px; margin-top: 12px;">
                    <li class="text-muted">Fill out basic competition details</li>
                    <li class="text-muted">Configure privacy and voting settings</li>
                    <li class="text-muted">Invite participants and judges</li>
                    <li class="text-muted">Set up submission requirements</li>
                    <li class="text-muted">Configure awards and certificates</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        // Data structure to store competitions
        let competitions = [{
                id: 'comp1',
                title: 'Spring 2023 Research Symposium',
                status: 'active',
                date: 'Apr 15, 2023',
                organization: 'University of Technology',
                email: 'research@university.edu',
                description: 'Annual research symposium showcasing student projects',
                privacy: 'public',
                voting: 'judges',
                submissionType: 'file',
                maxFileSize: 10,
                allowedFormats: ['pdf', 'doc', 'docx'],
                awards: [{
                        name: 'First Place',
                        value: 1000
                    },
                    {
                        name: 'Second Place',
                        value: 500
                    }
                ]
            },
            {
                id: 'comp2',
                title: 'Summer Innovation Challenge',
                status: 'completed',
                date: 'Jul 10, 2023',
                organization: 'Innovate Inc.',
                email: 'events@innovate.com',
                description: 'Summer challenge for innovative tech solutions',
                privacy: 'private',
                voting: 'public',
                submissionType: 'url',
                maxFileSize: 5,
                allowedFormats: ['pdf', 'ppt', 'pptx'],
                awards: [{
                        name: 'Grand Prize',
                        value: 5000
                    },
                    {
                        name: 'Innovation Award',
                        value: 2500
                    }
                ]
            },
            {
                id: 'comp3',
                title: 'Fall Design Competition',
                status: 'upcoming',
                date: 'Oct 5, 2023',
                organization: 'Design Guild',
                email: 'competitions@designguild.org',
                description: 'Annual design competition for creative professionals',
                privacy: 'public',
                voting: 'hybrid',
                submissionType: 'file',
                maxFileSize: 20,
                allowedFormats: ['jpg', 'png', 'psd', 'ai'],
                awards: [{
                        name: 'Best Design',
                        value: 3000
                    },
                    {
                        name: 'People\'s Choice',
                        value: 1500
                    }
                ]
            }
        ];

        // Tab management
        const tabs = document.querySelectorAll('.tab');
        const tabContent = document.getElementById('tabContent');
        const prevBtn = document.getElementById('prevTab');
        const nextBtn = document.getElementById('nextTab');
        const submitBtn = document.getElementById('submitBtn');
        const submitSpinner = document.getElementById('submitSpinner');
        const headerSpinner = document.getElementById('headerSpinner');
        const progressFill = document.getElementById('progressFill');
        const form = document.getElementById('competitionForm');

        // --- Access/Visibility sync (added) ---
        function syncAccessVisibility() {
            const access = document.querySelector('input[name="access_mode"]:checked')?.value;
            const visPublic = document.getElementById('visibility_public');
            const visPrivate = document.getElementById('visibility_private');

            if (access === 'private') {
                // Force visibility to private
                if (visPrivate) visPrivate.checked = true;
            } else {
                // If visibility is private but access isn't private, keep them consistent
                if (visPrivate && visPrivate.checked && access !== 'private' && visPublic) {
                    visPublic.checked = true;
                }
            }
        }

        document.addEventListener('change', (e) => {
            if (e.target && (e.target.name === 'access_mode' || e.target.name === 'visibility')) {
                if (e.target.name === 'access_mode') {
                    syncAccessVisibility();
                } else if (e.target.name === 'visibility') {
                    const vis = document.querySelector('input[name="visibility"]:checked')?.value;
                    const accessPrivate = document.getElementById('access_private');
                    const accessOpen = document.getElementById('access_open');
                    const accessApply = document.getElementById('access_apply');
                    if (vis === 'private') {
                        if (accessPrivate) accessPrivate.checked = true;
                    } else {
                        // default to open when public visibility selected
                        if (accessOpen && (document.querySelector('input[name="access_mode"]:checked')?.value === 'private')) {
                            accessOpen.checked = true;
                        }
                    }
                }
            }
        });


        const tabNames = ['basic', 'details', 'privacy', 'people', 'submission', 'awards', 'advanced'];
        let currentTab = 0;

        // Load tab content
        function loadTab(index) {
            currentTab = index;

            // Update tab UI
            tabs.forEach((tab, i) => {
                if (i === index) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Show/hide navigation buttons
            if (index === 0) {
                prevBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'inline-flex';
            }

            if (index === tabs.length - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-flex';
            } else {
                nextBtn.style.display = 'inline-flex';
                submitBtn.style.display = 'none';
            }

            // Update progress bar
            const progressPercentage = ((index + 1) / tabs.length) * 100;
            progressFill.style.width = `${progressPercentage}%`;

            // Load appropriate tab content
            const tabName = tabNames[index];
            let tabHTML = '';

            switch (tabName) {
                case 'basic':
                    tabHTML = `
                        <div id="tab-basic" class="tab-pane active">
                            <fieldset>
                                <legend>1. Organizer & Branding</legend>
                                <div class="form-group">
                                    <label for="organization">Organization <span class="text-error">*</span></label>
                                    <input type="text" id="organization" name="org_name" placeholder="Your organization name" required>
                                    <div class="error-message" id="orgError">Please enter an organization name</div>
                                    <div class="text-muted">This will be displayed to participants and judges</div>
                                </div>
                                <div class="form-group">
                                    <label for="org_email">Organizer Contact Email <span class="text-error">*</span></label>
                                    <input type="email" id="org_email" name="org_email" placeholder="organizer@example.com" required>
                                    <div class="error-message" id="emailError">Please enter a valid email address</div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="logo">Logo (optional)</label>
                                        <input type="file" id="logo" name="logo" accept="image/*">
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    `;
                    break;

                case 'details':
                    tabHTML = `
                        <div id="tab-details" class="tab-pane">
                            <fieldset>
                                <legend>2. Competition Details</legend>
                                <div class="form-group">
                                    <label for="comp_title">Competition Title <span class="text-error">*</span></label>
                                    <input type="text" id="comp_title" name="comp_title" placeholder="e.g., Annual Innovation Challenge" required>
                                    <div class="error-message" id="titleError">Please enter a competition title</div>
                                </div>
                                <div class="form-group">
                                    <label for="comp_description">Description <span class="text-error">*</span></label>
                                    <textarea id="comp_description" name="comp_description" rows="4" placeholder="Describe the competition, its purpose, and what participants can expect..." required></textarea>
                                    <div class="error-message" id="descError">Please enter a competition description</div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="start_date">Start Date <span class="text-error">*</span></label>
                                        <input type="date" id="start_date" name="start_date" required>
                                        <div class="error-message" id="startDateError">Please select a start date</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_date">End Date <span class="text-error">*</span></label>
                                        <input type="date" id="end_date" name="end_date" required>
                                        <div class="error-message" id="endDateError">Please select an end date</div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="timezone">Timezone</label>
                                        <select id="timezone" name="timezone">
                                            <option value="EST">Eastern Time (EST)</option>
                                            <option value="PST">Pacific Time (PST)</option>
                                            <option value="CST">Central Time (CST)</option>
                                            <option value="MST">Mountain Time (MST)</option>
                                            <option value="GMT">Greenwich Mean Time (GMT)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select id="category" name="category">
                                            <option value="academic">Academic</option>
                                            <option value="business">Business</option>
                                            <option value="technology">Technology</option>
                                            <option value="design">Design</option>
                                            <option value="arts">Arts</option>
                                            <option value="sciences">Sciences</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            
        <div class="form-row">
            <div class="form-group">
                <label for="timezone">Timezone</label>
                <select id="timezone" name="timezone">
                    <option value="America/Chicago">America/Chicago (CT)</option>
                    <option value="America/New_York">America/New_York (ET)</option>
                    <option value="America/Los_Angeles">America/Los_Angeles (PT)</option>
                    <option value="UTC">UTC</option>
                </select>
            </div>
            <div class="form-group">
                <label for="venue_link">Room / Meeting Link</label>
                <input type="text" id="venue_link" name="venue_link" placeholder="Building & room, or Zoom/Meet link">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="session_track">Session / Track</label>
                <input type="text" id="session_track" name="session_track" placeholder="e.g., AI, Bio, Design">
                <div class="text-muted">Use commas for multiple tracks</div>
            </div>
            <div class="form-group">
                <label for="slot_minutes">Presentation Duration (minutes)</label>
                <input type="number" id="slot_minutes" name="slot_minutes" min="5" max="60" value="12">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buffer_minutes">Buffer Between Presentations (minutes)</label>
                <input type="number" id="buffer_minutes" name="buffer_minutes" min="0" max="30" value="3">
            </div>
            <div class="form-group">
                <label>Presentation Order</label>
                <div class="checkbox-item">
                    <input type="checkbox" id="randomize_order" name="randomize_order" checked>
                    <label for="randomize_order">Randomize</label>
                </div>
                <div class="checkbox-item" style="margin-top:6px;">
                    <input type="checkbox" id="lock_order" name="lock_order">
                    <label for="lock_order">Lock order after publishing</label>
                </div>
            </div>
        </div>
    </fieldset>
                        </div>
                    `;
                    break;

                case 'privacy':
                    tabHTML = `
                        <div id="tab-privacy" class="tab-pane">
                            <fieldset>
                                <legend>3. Privacy & Voting</legend>
                                <div class="form-group">
                                    <label>Competition Visibility <span class="text-error">*</span></label>
                                    <div style="margin-top: 8px;">
                                        <input type="radio" id="visibility_public" name="visibility" value="public" checked>
                                        <label for="visibility_public" style="display: inline; margin-left: 8px;">Public - Anyone can view and participate</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="visibility_private" name="visibility" value="private">
                                        <label for="visibility_private" style="display: inline; margin-left: 8px;">Private - Only invited participants can join</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Participation Access <span class="text-error">*</span></label>
                                    <div class="align-options option-list" style="margin-top: 8px;">
                                        <div class="radio-item">
                                            <input type="radio" id="access_open" name="access_mode" value="open" checked>
                                            <label for="access_open">Open to all — anyone can join immediately</label>
                                        </div>
                                        <div class="radio-item">
                                            <input type="radio" id="access_apply" name="access_mode" value="apply">
                                            <label for="access_apply">Application required — applicants must be accepted by the host</label>
                                        </div>
                                        <div class="radio-item">
                                            <input type="radio" id="access_private" name="access_mode" value="private">
                                            <label for="access_private">Private / invite‑only — only invited users can join</label>
                                        </div>
                                    </div>
                                    <div class="text-muted">Tip: choosing <em>Private</em> will also set visibility to Private.</div>
                                </div>


                                
                                <div class="form-group">
                                    <label>Voting System <span class="text-error">*</span></label>
                                    <div style="margin-top: 8px;">
                                        <input type="radio" id="voting_judges" name="voting" value="judges" checked>
                                        <label for="voting_judges" style="display: inline; margin-left: 8px;">Judges Only - Only assigned judges can vote</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="voting_public" name="voting" value="public">
                                        <label for="voting_public" style="display: inline; margin-left: 8px;">Public Voting - Anyone can vote</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="voting_hybrid" name="voting" value="hybrid">
                                        <label for="voting_hybrid" style="display: inline; margin-left: 8px;">Hybrid - Judges and public voting combined</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="max_votes">Maximum Votes per Participant</label>
                                    <input type="number" id="max_votes" name="max_votes" min="1" value="3">
                                    <div class="text-muted">Applies only to public voting</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="result_visibility">Results Visibility</label>
                                    <select id="result_visibility" name="result_visibility">
                                        <option value="always">Always visible to everyone</option>
                                        <option value="after_voting">Visible after voting ends</option>
                                        <option value="after_closing">Visible after competition closes</option>
                                        <option value="judges_only">Only visible to judges</option>
                                    </select>
                                </div>
                            </fieldset>
                        </div>
                    `;
                    break;

                case 'people':
                    tabHTML = `
                        <div id="tab-people" class="tab-pane">
                            <fieldset>
                                <legend>4. People & Roles</legend>

                                <div class="form-group">
                                    <label>Judges</label>
                                    <div class="text-muted">Add judges who will evaluate submissions</div>
                                    <div id="judgesList"></div>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="addJudgeRow()">Add Judge</button>
                                    <div class="text-muted" style="margin-top: 8px;">Each judge can have a title and optional resume (PDF/DOC) and LinkedIn profile.</div>
                                </div>

                                <div class="form-group">
                                    <label>Participants</label>
                                    <div class="text-muted">Add participants (optional for private competitions)</div>
                                    <div id="participantsList"></div>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="addParticipantRow()">Add Participant</button>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="max_participants">Maximum Participants</label>
                                        <input type="number" id="max_participants" name="max_participants" min="1" placeholder="No limit">
                                        <div class="text-muted">Leave empty for no limit</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="max_submissions">Maximum Submissions per Participant</label>
                                        <input type="number" id="max_submissions" name="max_submissions" min="1" value="1">
                                    </div>
                                </div>
                            
        <div class="form-group">
            <label for="judge_calibration">Judge Calibration Notes</label>
            <textarea id="judge_calibration" name="judge_calibration" rows="3" placeholder="Explain scoring examples, what '5/Excellent' means, etc."></textarea>
        </div>
    </fieldset>
                        </div>
                    `;
                    break;

                case 'submission':
                    tabHTML = `
                        <div id="tab-submission" class="tab-pane">
                            <fieldset>
                                <legend>5. Submission Requirements</legend>
                                
                                <div class="form-group">
                                    <label>Submission Type <span class="text-error">*</span></label>
                                    <div style="margin-top: 8px;">
                                        <input type="radio" id="submission_file" name="submission_type" value="file" checked>
                                        <label for="submission_file" style="display: inline; margin-left: 8px;">File Upload</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="submission_url" name="submission_type" value="url">
                                        <label for="submission_url" style="display: inline; margin-left: 8px;">URL Submission</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="submission_text" name="submission_type" value="text">
                                        <label for="submission_text" style="display: inline; margin-left: 8px;">Text Entry</label>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="max_file_size">Maximum File Size (MB)</label>
                                        <input type="number" id="max_file_size" name="max_file_size" min="1" value="10">
                                        <div class="text-muted">Applies to file uploads only</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="allowed_formats">Allowed File Formats</label>
                                        <select id="allowed_formats" name="allowed_formats[]" multiple style="height: 100px;">
                                            <option value="pdf">PDF</option>
                                            <option value="doc">DOC</option>
                                            <option value="docx">DOCX</option>
                                            <option value="jpg">JPG</option>
                                            <option value="png">PNG</option>
                                            <option value="zip">ZIP</option>
                                            <option value="mp4">MP4</option>
                                            <option value="mov">MOV</option>
                                        </select>
                                        <div class="text-muted">Hold Ctrl/Cmd to select multiple</div>
                                    </div>
        <div class="form-group">
            <label for="rubric_pdf">Competition Rubric (PDF)</label>
            <input type="file" id="rubric_pdf" name="rubric_pdf" accept="application/pdf">
            <div class="text-muted">Upload a detailed scoring rubric as a PDF (optional).</div>
        </div>
        
                                </div>
                                
                                <div class="form-group">
                                    <label for="submission_guidelines">Submission Guidelines</label>
                                    <textarea id="submission_guidelines" name="submission_guidelines" rows="4" placeholder="Provide detailed guidelines for participants..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="custom_fields">Custom Submission Fields (JSON)</label>
                                    <textarea id="custom_fields" name="custom_fields" rows="4" placeholder='[{"name": "field1", "type": "text", "label": "Custom Field", "required": false}]'></textarea>
                                    <div class="text-muted">Add custom fields to the submission form using JSON format</div>
                                </div>
                            
        <div class="form-row">
            <div class="form-group">
                <label for="slide_deck">Slide Deck (required)</label>
                <input type="file" id="slide_deck" name="slide_deck" accept=".pdf,.ppt,.pptx" required>
                <div class="text-muted">Upload slides as PDF or PowerPoint.</div>
            </div>
            <div class="form-group">
                <label for="abstract_text">Abstract (text)</label>
                <textarea id="abstract_text" name="abstract_text" rows="3" placeholder="150–300 words describing the presentation."></textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="poster_image">Poster Image (optional)</label>
                <input type="file" id="poster_image" name="poster_image" accept=".png,.jpg,.jpeg">
            </div>
            <div class="form-group">
                <label>Consents</label>
                <div class="checkbox-item">
                    <input type="checkbox" id="consent_record" name="consent_record">
                    <label for="consent_record">I consent to recording/photography of my presentation</label>
                </div>
                <div class="checkbox-item" style="margin-top:6px;">
                    <input type="checkbox" id="consent_public" name="consent_public">
                    <label for="consent_public">I consent to public display of my slides/abstract</label>
                </div>
            </div>
        </div>
    </fieldset>
                        </div>
                    `;
                    break;

                case 'awards':
                    tabHTML = `
                        <div id="tab-awards" class="tab-pane">
                            <fieldset>
                                <legend>6. Awards & Prizes</legend>
                                
                                <div id="awardsContainer">
                                    <div class="award-item" style="margin-bottom: 16px; padding: 16px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Award Name</label>
                                                <input type="text" name="award_names[]" placeholder="e.g., First Place">
                                            </div>
                                            <div class="form-group">
                                                <label>Award Value</label>
                                                <input type="text" name="award_values[]" placeholder="e.g., $1000 or Gold Medal">
                                            </div>
                                        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Number of Winners</label>
                <input type="number" name="award_winner_counts[]" min="1" value="1">
            </div>
            <div class="form-group">
                <label>Per‑Winner Prize (optional)</label>
                <input type="text" name="award_per_winner_values[]" placeholder="e.g., $500 each">
            </div>
        </div>
        
                                        <button type="button" class="btn btn-secondary btn-sm remove-award">Remove</button>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-secondary" id="addAward">
                                    <i class="fas fa-plus"></i> Add Another Award
                                </button>
                                
                                <div class="form-group" style="margin-top: 20px;">
                                    <label for="certificate_template">Certificate Template</label>
                                    <select id="certificate_template" name="certificate_template">
                                        <option value="default">Default Template</option>
                                        <option value="elegant">Elegant Template</option>
                                        <option value="modern">Modern Template</option>
                                        <option value="custom">Custom Template</option>
                                    </select>
                                    <div class="text-muted">Certificates will be generated for winners, participants, and judges</div>
                                </div>
        <div class="form-group">
            <label for="sample_certificate">Sample Certificate (optional)</label>
            <input type="file" id="sample_certificate" name="sample_certificate" accept="application/pdf,image/*">
            <div class="text-muted">Upload a sample certificate design to use for winners.</div>
        </div>
        
                                
                                </fieldset>
                        </div>
                    `;
                    break;

                case 'advanced':
                    tabHTML = `
                        <div id="tab-advanced" class="tab-pane">
                            <fieldset>
                                <legend>7. Advanced Settings</legend>
                                
                                <div class="form-group">
                                    <label for="scoring_rubric">Scoring Rubric</label>
                                    <textarea id="scoring_rubric" name="scoring_rubric" rows="4" placeholder='[{"criteria": "Originality", "weight": 0.3, "description": "How original is the idea?"}]'></textarea>
                                    <div class="text-muted">Define your scoring criteria in JSON format</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="weighting_system">Score Weighting System</label>
                                    <select id="weighting_system" name="weighting_system">
                                        <option value="none">No weighting</option>
                                        <option value="normalized">Normalized scoring</option>
                                        <option value="weighted">Weighted criteria</option>
                                        <option value="custom">Custom formula</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="custom_css">Custom CSS</label>
                                    <textarea id="custom_css" name="custom_css" rows="4" placeholder="Add custom CSS to style your competition page"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="redirect_url">Redirect URL after Submission</label>
                                    <input type="url" id="redirect_url" name="redirect_url" placeholder="https://example.com/thank-you">
                                </div>
                                
                                
<div class="options-grid">
    <div class="form-group">
        <div class="switch-row">
            <input type="checkbox" id="enable_comments" name="enable_comments">
            <label for="enable_comments">
                Enable comments on submissions
                <span class="hint">Let viewers and judges leave feedback; can be moderated.</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="switch-row">
            <input type="checkbox" id="moderate_submissions" name="moderate_submissions">
            <label for="moderate_submissions">
                Moderate submissions before they are public
                <span class="hint">Submissions require organizer approval before appearing to others.</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="switch-row">
            <input type="checkbox" id="blind_review" name="blind_review">
            <label for="blind_review">
                Enable blind review (hide participant identities from judges)
                <span class="hint">Judge view hides names, emails, and affiliations during scoring.</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="switch-row">
            <input type="checkbox" id="conflict_disclosure" name="conflict_disclosure">
            <label for="conflict_disclosure">
                Require judges to self-report conflicts of interest
                <span class="hint">Judges confirm no conflicts before entering scores.</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label for="late_grace">Late Submission Grace Period (minutes)</label>
        <input type="number" id="late_grace" name="late_grace" min="0" value="0">
        <div class="text-muted">Allow a short window for late uploads without penalty.</div>
    </div>

    <div class="form-group">
        <label for="judging_visibility">Judging Visibility</label>
        <select id="judging_visibility" name="judging_visibility">
            <option value="private">Private (only judges & admins)</option>
            <option value="public_after_close">Public after competition closes</option>
            <option value="public_live">Public during competition</option>
        </select>
    </div>

    <div class="form-group">
        <label for="webhook_url">Webhook URL (optional)</label>
        <input type="url" id="webhook_url" name="webhook_url" placeholder="https://yourapp.com/webhooks/paperet">
        <div class="text-muted">Receive real-time events for new submissions, status changes, and awards.</div>
    </div>
</div>
</div>
            </div>

            
<div class="form-group">
    <label>Export Options</label>
    <div class="align-options" style="margin-top: 8px; flex-wrap: wrap; gap: 12px;">
        <label class="checkbox-item"><input type="checkbox" name="export_options[]" value="csv"> <span>CSV</span></label>
        <label class="checkbox-item"><input type="checkbox" name="export_options[]" value="json"> <span>JSON</span></label>
        <label class="checkbox-item"><input type="checkbox" name="export_options[]" value="pdf"> <span>PDF Summary</span></label>
    </div>
</div>
    
        <div class="form-row">
            <div class="form-group">
                <label for="score_scale">Per-criterion Score Scale</label>
                <select id="score_scale" name="score_scale">
                    <option value="1-5">1–5</option>
                    <option value="1-10">1–10</option>
                </select>
                <div class="text-muted">Used when normalizing totals.</div>
            </div>
            <div class="form-group">
                <label for="tiebreak_policy">Tie-break Policy</label>
                <select id="tiebreak_policy" name="tiebreak_policy">
                    <option value="highest_impact">Highest score on "Impact"</option>
                    <option value="chair_vote">Chair decision</option>
                    <option value="earliest_submission">Earliest submission</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="qa_minutes">Q&A Time per Presentation (minutes)</label>
                <input type="number" id="qa_minutes" name="qa_minutes" min="0" max="30" value="3">
            </div>
            <div class="form-group">
                <label for="leaderboard_visibility">Live Leaderboard Visibility</label>
                <select id="leaderboard_visibility" name="leaderboard_visibility">
                    <option value="judges_only">Judges only</option>
                    <option value="public_after_close">Public after competition closes</option>
                    <option value="public_live">Public during competition</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email Notifications</label>
                <div class="checkbox-item">
                    <input type="checkbox" id="notify_submission" name="notify_options[]" value="submission" checked>
                    <label for="notify_submission">Notify on new submission</label>
                </div>
                <div class="checkbox-item" style="margin-top:6px;">
                    <input type="checkbox" id="notify_schedule" name="notify_options[]" value="schedule">
                    <label for="notify_schedule">Send schedule to presenters & judges</label>
                </div>
                <div class="checkbox-item" style="margin-top:6px;">
                    <input type="checkbox" id="notify_results" name="notify_options[]" value="results">
                    <label for="notify_results">Email winners when results are published</label>
                </div>
            </div>
            <div class="form-group">
                <label for="results_publish_date">Results Publish Date (optional)</label>
                <input type="date" id="results_publish_date" name="results_publish_date">
            </div>
        </div>

        <div class="form-group">
            <label for="announcement_template">Winner Announcement Template (Email)</label>
            <textarea id="announcement_template" name="announcement_template" rows="4" placeholder="Subject: Congratulations!

Dear {name},
You have been selected as {award} ..."></textarea>
        </div>
    </fieldset>
        </div>
        `;
                    break;
            }

            tabContent.innerHTML = tabHTML;

            // Initialize tag inputs
            if (tabName === 'people') {
                initTagInputs();
            }

            // Initialize award adding
            if (tabName === 'awards') {
                initAwards();
            }
        }

        // Initialize tag inputs for judges and participants
        function initTagInputs() {
            const judgeInput = document.getElementById('judgeInput');
            const participantInput = document.getElementById('participantInput');

            judgeInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const email = this.value.trim();
                    if (email && isValidEmail(email)) {
                        addTag('judgesContainer', email, judgeInput);
                    } else {
                        showToast('Please enter a valid email address', 'error');
                    }
                }
            });

            participantInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const email = this.value.trim();
                    if (email && isValidEmail(email)) {
                        addTag('participantsContainer', email, participantInput);
                    } else {
                        showToast('Please enter a valid email address', 'error');
                    }
                }
            });
        }

        // Initialize award adding functionality
        function initAwards() {
            const addAwardBtn = document.getElementById('addAward');
            const awardsContainer = document.getElementById('awardsContainer');

            addAwardBtn?.addEventListener('click', function() {
                const awardItem = document.createElement('div');
                awardItem.className = 'award-item';
                awardItem.style.marginBottom = '16px';
                awardItem.style.padding = '16px';
                awardItem.style.border = '1px solid var(--border)';
                awardItem.style.borderRadius = 'var(--radius-sm)';

                awardItem.innerHTML = `
                    <div class="form-row">
                        <div class="form-group">
                            <label>Award Name</label>
                            <input type="text" name="award_names[]" placeholder="e.g., First Place">
                        </div>
                        <div class="form-group">
                            <label>Award Value</label>
                            <input type="text" name="award_values[]" placeholder="e.g., $1000 or Gold Medal">
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm remove-award">Remove</button>
                `;

                awardsContainer.appendChild(awardItem);

                // Add event listener to remove button
                const removeBtn = awardItem.querySelector('.remove-award');
                removeBtn.addEventListener('click', function() {
                    awardsContainer.removeChild(awardItem);
                });
            });

            // Add event listeners to existing remove buttons
            const removeButtons = awardsContainer.querySelectorAll('.remove-award');
            removeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    awardsContainer.removeChild(btn.closest('.award-item'));
                });
            });
        }

        // Add tag to tag container
        function addTag(containerId, value, inputElement) {
            const container = document.getElementById(containerId);
            const tag = document.createElement('div');
            tag.className = 'tag';
            tag.innerHTML = `
                ${value}
                <span class="tag-remove" onclick="this.parentElement.remove()">&times;</span>
            `;
            container.insertBefore(tag, inputElement);
            inputElement.value = '';
        }

        // Validate email format
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            let icon = 'fa-check-circle';
            if (type === 'error') icon = 'fa-exclamation-circle';
            if (type === 'info') icon = 'fa-info-circle';

            toast.innerHTML = `
                <i class="fas ${icon}"></i>
                <span>${message}</span>
            `;

            document.getElementById('toastContainer').appendChild(toast);

            // Show toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Hide toast after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

        // View competition details
        function viewCompetition(id) {
            const competition = competitions.find(c => c.id === id);
            if (!competition) return;

            document.getElementById('modalTitle').textContent = competition.title;

            let content = `
                <div style="margin-bottom: 20px;">
                    <p><strong>Status:</strong> <span class="badge badge-${competition.status === 'active' ? 'success' : competition.status === 'completed' ? 'warning' : 'info'}">${competition.status}</span></p>
                    <p><strong>Date:</strong> ${competition.date}</p>
                    <p><strong>Organization:</strong> ${competition.organization}</p>
                    <p><strong>Contact:</strong> ${competition.email}</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h3>Description</h3>
                    <p>${competition.description}</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <h3>Privacy Settings</h3>
                        <p>Visibility: ${competition.privacy}</p>
                        <p>Voting: ${competition.voting}</p>
                    </div>
                    <div class="form-group">
                        <h3>Submission Details</h3>
                        <p>Type: ${competition.submissionType}</p>
                        <p>Max File Size: ${competition.maxFileSize}MB</p>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h3>Awards</h3>
                    <ul>
            `;

            competition.awards.forEach(award => {
                content += `<li>${award.name}: ${award.value}</li>`;
            });

            content += `
                    </ul>
                </div>
                
                <div class="d-flex justify-between">
                    <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                    <button class="btn" onclick="editCompetition('${competition.id}')">Edit Competition</button>
                </div>
            `;

            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('competitionModal').style.display = 'flex';
        }

        // Edit competition
        function editCompetition(id) {
            const competition = competitions.find(c => c.id === id);
            if (!competition) return;

            document.getElementById('modalTitle').textContent = `Edit: ${competition.title}`;

            let content = `
                <form id="editCompetitionForm">
                    <div class="form-group">
                        <label for="edit_title">Competition Title</label>
                        <input type="text" id="edit_title" value="${competition.title}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" rows="3" required>${competition.description}</textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_organization">Organization</label>
                            <input type="text" id="edit_organization" value="${competition.organization}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Contact Email</label>
                            <input type="email" id="edit_email" value="${competition.email}" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-between">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn">Save Changes</button>
                    </div>
                </form>
            `;

            document.getElementById('modalContent').innerHTML = content;

            // Add form submission handler
            document.getElementById('editCompetitionForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Update competition data
                competition.title = document.getElementById('edit_title').value;
                competition.description = document.getElementById('edit_description').value;
                competition.organization = document.getElementById('edit_organization').value;
                competition.email = document.getElementById('edit_email').value;

                showToast('Competition updated successfully');
                closeModal();

                // Refresh competitions list
                renderCompetitions();
            });

            document.getElementById('competitionModal').style.display = 'flex';
        }

        // Close modal
        function closeModal() {
            document.getElementById('competitionModal').style.display = 'none';
        }

        // Render competitions list
        function renderCompetitions() {
            const container = document.getElementById('competitionsList');
            const countElement = document.getElementById('competitionCount');

            countElement.textContent = competitions.length;

            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            competitions.forEach(comp => {
                let badgeClass = 'badge-info';
                if (comp.status === 'active') badgeClass = 'badge-success';
                if (comp.status === 'completed') badgeClass = 'badge-warning';

                html += `
                    <tr>
                        <td>
                            <div style="font-weight: 600;">${comp.title}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                ${comp.date}
                            </div>
                        </td>
                        <td><span class="badge ${badgeClass}">${comp.status}</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm" onclick="viewCompetition('${comp.id}')">View</button>
                                <button class="btn btn-sm btn-secondary" onclick="editCompetition('${comp.id}')">Edit</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            container.innerHTML = html;
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            submitSpinner.style.display = 'block';
            submitBtn.querySelector('span').textContent = 'Creating...';
            submitBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                // Create a mock competition
                const newCompetition = {
                    id: 'comp' + (competitions.length + 1),
                    title: document.getElementById('comp_title')?.value || 'New Competition',
                    status: 'upcoming',
                    date: new Date().toLocaleDateString(),
                    organization: document.getElementById('organization')?.value || 'Your Organization',
                    email: document.getElementById('org_email')?.value || 'email@example.com',
                    description: document.getElementById('comp_description')?.value || 'Competition description',
                    privacy: document.querySelector('input[name="visibility"]:checked')?.value || 'public',
                    accessMode: document.querySelector('input[name="access_mode"]:checked')?.value || (document.querySelector('input[name="visibility"]:checked')?.value === 'private' ? 'private' : 'open'),
                    requiresApproval: (document.querySelector('input[name="access_mode"]:checked')?.value === 'apply'),
                    voting: document.querySelector('input[name="voting"]:checked')?.value || 'judges',
                    submissionType: document.querySelector('input[name="submission_type"]:checked')?.value || 'file',
                    maxFileSize: document.getElementById('max_file_size')?.value || 10,
                    awards: [{
                        name: 'First Place',
                        value: 'Prize'
                    }]
                };

                competitions.push(newCompetition);

                // Reset form and UI
                form.reset();
                submitSpinner.style.display = 'none';
                submitBtn.querySelector('span').textContent = 'Create Competition';
                submitBtn.disabled = false;

                // Show success message
                showToast('Competition created successfully!');

                // Refresh competitions list
                renderCompetitions();

                // Reset to first tab
                currentTab = 0;
                loadTab(currentTab);
            }, 1500);
        });

        // Navigation between tabs
        nextBtn.addEventListener('click', function() {
            // Validate current tab before proceeding
            if (validateCurrentTab()) {
                if (currentTab < tabs.length - 1) {
                    loadTab(currentTab + 1);
                }
            }
        });

        prevBtn.addEventListener('click', function() {
            if (currentTab > 0) {
                loadTab(currentTab - 1);
            }
        });

        // Tab click handlers
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                loadTab(index);
            });
        });

        // Validate current tab before proceeding
        function validateCurrentTab() {
            const tabName = tabNames[currentTab];

            if (tabName === 'basic') {
                const orgName = document.getElementById('organization');
                const orgEmail = document.getElementById('org_email');

                if (!orgName.value.trim()) {
                    document.getElementById('orgError').style.display = 'block';
                    orgName.classList.add('error');
                    return false;
                } else {
                    document.getElementById('orgError').style.display = 'none';
                    orgName.classList.remove('error');
                }

                if (!orgEmail.value.trim() || !isValidEmail(orgEmail.value)) {
                    document.getElementById('emailError').style.display = 'block';
                    orgEmail.classList.add('error');
                    return false;
                } else {
                    document.getElementById('emailError').style.display = 'none';
                    orgEmail.classList.remove('error');
                }
            }

            if (tabName === 'details') {
                const compTitle = document.getElementById('comp_title');
                const compDesc = document.getElementById('comp_description');
                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');

                if (!compTitle.value.trim()) {
                    // Create error element if it doesn't exist
                    if (!document.getElementById('titleError')) {
                        const errorEl = document.createElement('div');
                        errorEl.className = 'error-message';
                        errorEl.id = 'titleError';
                        errorEl.textContent = 'Please enter a competition title';
                        compTitle.parentNode.appendChild(errorEl);
                    }
                    document.getElementById('titleError').style.display = 'block';
                    compTitle.classList.add('error');
                    return false;
                } else {
                    if (document.getElementById('titleError')) {
                        document.getElementById('titleError').style.display = 'none';
                    }
                    compTitle.classList.remove('error');
                }

                if (!compDesc.value.trim()) {
                    if (!document.getElementById('descError')) {
                        const errorEl = document.createElement('div');
                        errorEl.className = 'error-message';
                        errorEl.id = 'descError';
                        errorEl.textContent = 'Please enter a competition description';
                        compDesc.parentNode.appendChild(errorEl);
                    }
                    document.getElementById('descError').style.display = 'block';
                    compDesc.classList.add('error');
                    return false;
                } else {
                    if (document.getElementById('descError')) {
                        document.getElementById('descError').style.display = 'none';
                    }
                    compDesc.classList.remove('error');
                }

                // Set today's date as default for start date if empty
                if (!startDate.value) {
                    const today = new Date();
                    const yyyy = today.getFullYear();
                    let mm = today.getMonth() + 1;
                    let dd = today.getDate();

                    if (dd < 10) dd = '0' + dd;
                    if (mm < 10) mm = '0' + mm;

                    startDate.value = `${yyyy}-${mm}-${dd}`;
                }

                // Set end date to 7 days from start if empty
                if (!endDate.value && startDate.value) {
                    const start = new Date(startDate.value);
                    start.setDate(start.getDate() + 7);

                    const yyyy = start.getFullYear();
                    let mm = start.getMonth() + 1;
                    let dd = start.getDate();

                    if (dd < 10) dd = '0' + dd;
                    if (mm < 10) mm = '0' + mm;

                    endDate.value = `${yyyy}-${mm}-${dd}`;
                }
            }

            return true;
        }

        // Initialize the application
        function init() {
            loadTab(0);
            renderCompetitions();

            // Set default dates
            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1;
            let dd = today.getDate();

            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;

            const todayStr = `${yyyy}-${mm}-${dd}`;

            // Set default start date to today
            const startDate = document.getElementById('start_date');
            if (startDate) {
                startDate.value = todayStr;
                startDate.min = todayStr;
            }

            // Set default end date to 7 days from today
            const endDate = document.getElementById('end_date');
            if (endDate) {
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);

                const nwYYYY = nextWeek.getFullYear();
                let nwMM = nextWeek.getMonth() + 1;
                let nwDD = nextWeek.getDate();

                if (nwDD < 10) nwDD = '0' + nwDD;
                if (nwMM < 10) nwMM = '0' + nwMM;

                endDate.value = `${nwYYYY}-${nwMM}-${nwDD}`;
                endDate.min = todayStr;
            }
        }

        // Start the application
        init();
    </script>

    <script>
        function removeRow(btn) {
            const item = btn.closest('.line-item') || btn.closest('.award-item') || btn.closest('.row-item');
            if (item) item.remove();
        }

        function addJudgeRow() {
            const wrap = document.getElementById('judgesList');
            if (!wrap) return;
            const div = document.createElement('div');
            div.className = 'row-item';
            div.style = 'padding:12px; border:1px solid var(--border); border-radius: var(--radius-sm); margin-bottom:8px;';
            div.innerHTML = `
            <div class="form-row">
                <div class="form-group"><label>Name</label><input type="text" name="judges[name][]" placeholder="Full name"></div>
                <div class="form-group"><label>Email</label><input type="email" name="judges[email][]" placeholder="judge@example.com"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Title</label><input type="text" name="judges[title][]" placeholder="e.g., Professor of CS"></div>
                <div class="form-group"><label>LinkedIn (optional)</label><input type="url" name="judges[linkedin][]" placeholder="https://linkedin.com/in/..."></div>
            </div>
            <div class="form-group"><label>Resume (optional)</label><input type="file" name="judges[resume][]" accept="application/pdf,.doc,.docx"></div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)">Remove Judge</button>
        `;
            wrap.appendChild(div);
        }

        function addParticipantRow() {
            const wrap = document.getElementById('participantsList');
            if (!wrap) return;
            const div = document.createElement('div');
            div.className = 'row-item';
            div.style = 'padding:12px; border:1px solid var(--border); border-radius: var(--radius-sm); margin-bottom:8px;';
            div.innerHTML = `
            <div class="form-row">
                <div class="form-group"><label>Name</label><input type="text" name="participants[name][]" placeholder="Full name"></div>
                <div class="form-group"><label>Email</label><input type="email" name="participants[email][]" placeholder="student@example.com"></div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)">Remove Participant</button>
        `;
            wrap.appendChild(div);
        }
    </script>

</body>

</html>