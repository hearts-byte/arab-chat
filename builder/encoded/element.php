<?php
require_once "../config_install.php";

if (($_POST["check"] ?? null) !== "1") {
    http_response_code(403);
    die;
}
?>
<style>
.install-container {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}
.install-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.progress-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}
.step-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex: 1;
    position: relative;
}
.step-btn i {
    font-size: 1.25rem;
    padding: 10px;
    border-radius: 50%;
    background: var(--bg-base);
    border: 2px solid var(--border);
    transition: all 0.2s;
}
.step-btn.active { color: var(--text-main); }
.step-btn.active i { background: var(--primary); border-color: var(--primary); color: #fff; }
.step-btn.disabled { cursor: not-allowed; opacity: 0.5; }
.installer-step { display: none; animation: fadeIn 0.3s ease; }
.installer-step.active { display: block; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.step-title {
    font-size: 1.25rem;
    margin-bottom: 20px;
    color: var(--text-main);
    font-weight: 600;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
@media (max-width: 480px) {
    .form-grid { grid-template-columns: 1fr; }
    .step-btn span { display: none; }
    .install-card { padding: 20px; }
}
.form-group { margin-bottom: 15px; }
.form-group.full { grid-column: 1 / -1; }
.form-label {
    display: block;
    margin-bottom: 8px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.form-control {
    width: 100%;
    padding: 12px;
    background: var(--bg-base);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-main);
    font-size: 0.9rem;
    font-family: var(--font);
    transition: border-color 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--primary);
}
.form-control[readonly] {
    background: rgba(0,0,0,0.2);
    color: var(--text-muted);
    cursor: not-allowed;
}
.notice {
    background: rgba(0, 94, 255, 0.1);
    border: 1px solid var(--primary);
    padding: 12px;
    border-radius: var(--radius);
    font-size: 0.85rem;
    color: var(--text-main);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.notice i { color: var(--primary); }
.actions {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
}
.actions .btn { width: auto; min-width: 140px; }
</style>

<div class="install-container">
    <div class="install-card">
        <div class="progress-container">
            <button class="step-btn active" data-target="step_user">
                <i class="fa fa-user"></i><span>Owner</span>
            </button>
            <button class="step-btn disabled" data-target="step_site">
                <i class="fa fa-globe"></i><span>Site</span>
            </button>
            <button class="step-btn disabled" data-target="step_db">
                <i class="fa fa-database"></i><span>Database</span>
            </button>
            <button class="step-btn disabled" data-target="step_license">
                <i class="fa fa-key"></i><span>Status</span>
            </button>
        </div>

        <div class="installer-step active" id="step_user">
            <h3 class="step-title">Administrator Account</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input id="install_username" class="form-control required" type="text" placeholder="admin">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input id="install_email" class="form-control required" type="email" placeholder="admin@domain.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input id="install_password" class="form-control required" type="password">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input id="install_repeat" class="form-control required" type="password">
                </div>
            </div>
            <div class="actions">
                <button id="next_user" class="btn">Continue</button>
            </div>
        </div>

        <div class="installer-step" id="step_site">
            <h3 class="step-title">Site Configuration</h3>
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label">Installation URL</label>
                    <input id="install_domain" class="form-control required" type="text" placeholder="https://domain.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Site Title</label>
                    <input id="install_title" class="form-control required" type="text" placeholder="My Chat">
                </div>
                <div class="form-group">
                    <label class="form-label">Default Language</label>
                    <select id="install_language" class="form-control required">
                        <?php echo listLanguage("English", 1); ?>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button id="next_site" class="btn">Continue</button>
            </div>
        </div>

        <div class="installer-step" id="step_db">
            <h3 class="step-title">Database Credentials</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Hostname</label>
                    <input id="install_db_host" class="form-control required" type="text" value="localhost">
                </div>
                <div class="form-group">
                    <label class="form-label">Database Name</label>
                    <input id="install_db_name" class="form-control required" type="text">
                </div>
                <div class="form-group">
                    <label class="form-label">Database User</label>
                    <input id="install_db_user" class="form-control required" type="text">
                </div>
                <div class="form-group">
                    <label class="form-label">Database Password</label>
                    <input id="install_db_password" class="form-control required" type="password">
                </div>
            </div>
            <div class="actions">
                <button id="next_db" class="btn">Continue</button>
            </div>
        </div>

        <div class="installer-step" id="step_license">
            <h3 class="step-title">Installation Ready</h3>
            <div class="notice">
                <i class="fa fa-info-circle"></i> Have A Good Day!
            </div>
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label">License Status</label>
                    <input id="install_license" class="form-control" type="text" value="Active / Nulled Version" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Developer #1</label>
                    <input id="install_store_user" class="form-control" type="text" value="FXNTXM" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Developer #2</label>
                    <input id="install_store_pass" class="form-control" type="text" value="BlackHunter" readonly>
                </div>
            </div>
            <div class="actions">
                <button id="install_component" onclick="runInstaller()" class="btn">Install</button>
                <button id="wait_install" class="btn" style="display:none;" disabled>Installing...</button>
            </div>
        </div>
    </div>
</div>