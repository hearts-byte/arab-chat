<?php
if ($chat_install != 2) { 
    die; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Codychat 10.1 Installer</title>
<link rel="shortcut icon" href="default_images/icon.png">
<link rel="stylesheet" href="https://teens.mastivibe.com/chat/css/fontawesome/css/all.css">
<script src="js/jquery-3.5.1.min.js"></script>
<script src="js/jqueryui/jquery-ui.min.js"></script>
<script src="js/global.min.js"></script>
<script src="builder/install.js?v=11"></script>
<style>
:root {
    --primary: #005eff;
    --bg-base: #0b0f19;
    --bg-card: #111827;
    --border: #1f2937;
    --text-main: #f3f4f6;
    --text-muted: #9ca3af;
    --radius: 6px;
    --font: system-ui, -apple-system, sans-serif;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: var(--font);
    background: var(--bg-base);
    color: var(--text-main);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}
#install_content {
    width: 100%;
    max-width: 480px;
}
.card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.card-header {
    text-align: center;
    margin-bottom: 30px;
}
.card-header img {
    height: 40px;
    margin-bottom: 15px;
}
.card-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 5px;
}
.card-header p {
    color: var(--primary);
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.terms-box {
    background: rgba(0,0,0,0.2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 25px;
}
.terms-box ul {
    list-style: none;
    font-size: 0.875rem;
    color: var(--text-muted);
}
.terms-box li {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}
.terms-box li:last-child {
    margin-bottom: 0;
}
.terms-box li i {
    color: var(--primary);
    margin-right: 10px;
    font-size: 1rem;
}
.credits-text {
    text-align: center;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 25px;
    line-height: 1.5;
}
.agreement {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    margin-bottom: 25px;
    font-size: 0.875rem;
    user-select: none;
}
.agreement i {
    color: var(--primary);
    font-size: 1.2rem;
    margin-right: 10px;
}
.btn {
    width: 100%;
    background: var(--primary);
    color: #ffffff;
    border: none;
    padding: 14px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: var(--radius);
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn:hover {
    opacity: 0.9;
}
.btn:disabled {
    background: var(--border);
    color: var(--text-muted);
    cursor: not-allowed;
}
.popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-left: 4px solid var(--primary);
    padding: 15px 20px;
    border-radius: var(--radius);
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    gap: 12px;
    transform: translateX(120%);
    transition: transform 0.3s ease;
    z-index: 9999;
}
.popup.show {
    transform: translateX(0);
}
.popup i { font-size: 1.2rem; }
.popup .msg { font-size: 0.875rem; font-weight: 500; }
</style>
</head>
<body>
<div id="install_content">
    <div class="card">
        <div class="card-header">
            <img src="default_images/logo.png?v=11" alt="Logo">
            <h2>Codychat 10.1</h2>
            <p>Nulled Edition</p>
        </div>
        <div class="terms-box">
            <ul>
                <li><i class="fa fa-check"></i>Full features available</li>
                <li><i class="fa fa-check"></i>License requirements removed</li>
                <li><i class="fa fa-check"></i>Free to modify</li>
                <li><i class="fa fa-info-circle"></i>No official support provided</li>
            </ul>
        </div>
        <div class="credits-text">
            Provided by FXNTXM<br>
            Patched by BlackHunter
        </div>
        <div class="agreement">
            <i class="fa fa-circle accept_install" value="0"></i>
            <span>I acknowledge this is a nulled version</span>
        </div>
        <button id="start_install" class="btn" disabled>Proceed to Installation</button>
    </div>
</div>
<div class="popup" id="ui_popup">
    <i class="fa"></i>
    <div class="msg"></div>
</div>
</body>
</html>