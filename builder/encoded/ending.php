<?php
if (($_POST['check'] ?? null) !== '1') { 
    http_response_code(403); 
    exit; 
}
?>
<style>
.success-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    text-align: center;
    max-width: 400px;
    margin: 0 auto;
}
.success-icon {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 20px;
}
.success-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 10px;
}
.success-desc {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 30px;
    line-height: 1.6;
}
.info-box {
    background: rgba(0,0,0,0.2);
    border: 1px solid var(--border);
    padding: 15px;
    border-radius: var(--radius);
    margin-bottom: 30px;
}
.info-box p {
    font-size: 0.85rem;
    color: var(--text-main);
    margin: 5px 0;
}
.info-box .hl {
    color: var(--primary);
    font-weight: 600;
}
</style>

<div class="success-card">
    <div class="success-icon">
        <i class="fa fa-check-circle"></i>
    </div>
    <h3 class="success-title">Installation Complete</h3>
    <p class="success-desc">Codychat 10.1 has been successfully installed and configured on your server.</p>
    
    <div class="info-box">
        <p>Edition: <span class="hl">Nulled</span></p>
        <p>Features: <span class="hl">Fully Unlocked</span></p>
        <p>Developers: <span class="hl">FXNTXM & BlackHunter</span></p>
    </div>
    
    <button onclick="endInstall()" class="btn">Access Application</button>
</div>