    <!-- MODAL 1: AUTH LOGIN / REGISTER -->
    <div class="modal-overlay" id="authModal" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="authModalTitle">Login Account</h3>
                <button class="back-btn" onclick="closeModal('authModal')">✕</button>
            </div>
            <form onsubmit="handleAuthSubmit(event)">
                <div id="registerNameGroup" style="display: none;">
                    <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Full Name</label>
                    <input type="text" id="authName" class="select-input" placeholder="e.g. John Doe">
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Email Address</label>
                    <input type="email" id="authEmail" class="select-input" required placeholder="name@domain.com">
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Password</label>
                    <input type="password" id="authPassword" class="select-input" required minlength="6" placeholder="••••••••">
                </div>

                <button type="submit" class="action-btn" id="authSubmitBtn" style="width: 100%; margin-top: 1rem;">Login</button>
            </form>
            <div style="margin-top: 1rem; text-align: center; font-size: 0.82rem; color: var(--text-muted);">
                <a href="#" style="color: #06b6d4;" onclick="toggleAuthMode()">Switch between Login / Register</a>
            </div>
        </div>
    </div>

    <!-- MODAL 2: REDEEM ACTIVATION CODE -->
    <div class="modal-overlay" id="redeemModal" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>🔑 Redeem Serial Code</h3>
                <button class="back-btn" onclick="closeModal('redeemModal')">✕</button>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                Enter your serial code (e.g. PRO-89XK-K92L) to upgrade your account plan.
            </p>
            <form onsubmit="handleRedeemCode(event)">
                <input type="text" id="redeemCodeInput" class="select-input" required placeholder="PRO-XXXX-XXXX" style="text-transform: uppercase; font-weight: 700; letter-spacing: 2px; text-align: center;">
                <button type="submit" class="action-btn" style="width: 100%; margin-top: 1rem;">✨ Activate License</button>
            </form>
        </div>
    </div>

    <!-- MODAL 3: BATCH LIMIT / PLAN UPGRADE PROMPT -->
    <div class="modal-overlay" id="upgradeModal" style="display: none;">
        <div class="modal-card" style="text-align: center; max-width: 480px;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">💎</div>
            <h3 style="font-size: 1.4rem; font-weight: 800; color: #06b6d4; margin-bottom: 0.5rem;" id="upgradeModalTitle">Plan Upgrade Required</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;" id="upgradeModalMessage">
                Free accounts are limited to PDF & Document tools only. Upgrade to <strong style="color: #fff;">PRO</strong> for Image Converter or <strong style="color: #ec4899;">ENTERPRISE</strong> for AI Background Removal!
            </p>
            <div style="display: flex; gap: 0.8rem; justify-content: center;">
                <button class="action-btn" style="padding: 0.7rem 1.5rem;" onclick="closeModal('upgradeModal'); openPricingView();">💎 Upgrade Plan Now</button>
                <button class="back-btn" onclick="closeModal('upgradeModal')">Cancel</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: CUSTOM CONFIRMATION DIALOG -->
    <div class="modal-overlay" id="confirmModal" style="display: none;">
        <div class="modal-card" style="text-align: center; max-width: 440px;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;" id="confirmModalIcon">⚠️</div>
            <h3 style="font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;" id="confirmModalTitle">Are you sure?</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;" id="confirmModalMessage">
                This action cannot be undone.
            </p>
            <div style="display: flex; gap: 0.8rem; justify-content: center;">
                <button class="back-btn" id="confirmCancelBtn" onclick="closeModal('confirmModal')">Cancel</button>
                <button class="action-btn" id="confirmActionBtn" style="padding: 0.7rem 1.5rem;">Confirm</button>
            </div>
        </div>
    </div>

    <!-- GLOBAL TOAST NOTIFICATION CONTAINER -->
    <div id="toastContainer" style="position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; max-width: 400px; pointer-events: none;"></div>
