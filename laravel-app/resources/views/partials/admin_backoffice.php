        <!-- 3. ADMIN BACKOFFICE VIEW (MODERN SIDEBAR DASHBOARD LAYOUT) -->
        <div class="workspace-card" id="adminCard" style="display: none; padding: 0; overflow: hidden; border-radius: 24px;">
            <div style="display: flex; min-height: 680px;">
                
                <!-- SIDEBAR NAVIGATION -->
                <div class="admin-sidebar" style="width: 250px; background: rgba(11, 17, 32, 0.95); border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 1.5rem 1rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.8rem; padding-bottom: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 1.2rem;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #06b6d4, #3b82f6); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                            </div>
                            <div>
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: #fff; margin:0;">Admin Console</h3>
                                <span style="font-size: 0.72rem; color: #64748b;">Convertify Management</span>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.35rem;" id="adminSidebarNav">
                            <button type="button" class="admin-nav-item active" onclick="switchAdminTab('overview', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                                Overview & Stats
                            </button>
                            <button type="button" class="admin-nav-item" onclick="switchAdminTab('ldap', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                                LDAP Directory Auth
                            </button>
                            <button type="button" class="admin-nav-item" onclick="switchAdminTab('smtp', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                SMTP & Email Verification
                            </button>
                            <button type="button" class="admin-nav-item" onclick="switchAdminTab('payment', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                Pricing & Gateways
                            </button>
                            <button type="button" class="admin-nav-item" onclick="switchAdminTab('telegram', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Telegram Bot Notif
                            </button>
                            <button type="button" class="admin-nav-item" onclick="switchAdminTab('codes', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                                Activation Codes
                            </button>
                            <button type="button" class="admin-nav-item" onclick="switchAdminTab('users', this)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                User Management
                            </button>
                        </div>
                    </div>

                    <button type="button" class="back-btn" style="width: 100%; text-align: center; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" onclick="showToolsGrid()">
                        ← Exit Backoffice
                    </button>
                </div>

                <!-- RIGHT MAIN CONTENT WORKSPACE -->
                <div style="flex: 1; padding: 2rem; overflow-y: auto; max-height: 700px; background: rgba(15, 23, 42, 0.5);">
                    
                    <!-- TAB 1: OVERVIEW & STATS -->
                    <div id="adminTabOverview" class="admin-tab-content">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #3b82f6; margin:0 0 0.4rem 0;">📊 System Analytics & Overview</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Real-time metrics of processed files, active users, and serial activation codes</p>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; margin-bottom: 2rem;">
                            <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 1.5rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Total Files Converted</span>
                                <div style="font-size: 2.2rem; font-weight: 800; color: #06b6d4; margin-top: 0.3rem;" id="statTotalJobs">0</div>
                            </div>
                            <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 1.5rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Registered Users</span>
                                <div style="font-size: 2.2rem; font-weight: 800; color: #a78bfa; margin-top: 0.3rem;" id="statTotalUsers">0</div>
                            </div>
                            <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 1.5rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Active Activation Codes</span>
                                <div style="font-size: 2.2rem; font-weight: 800; color: #ec4899; margin-top: 0.3rem;" id="statTotalCodes">0</div>
                            </div>
                        </div>

                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem;">
                            <h3 style="font-size: 1.1rem; color: #fff; margin-bottom: 0.8rem;">⚡ Microservices Health Status</h3>
                            <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; margin:0;">
                                🟢 <strong style="color: #10b981;">ALL SYSTEMS OPERATIONAL</strong><br>
                                All microservices (Golang LibreOffice Worker, Python AI BG Worker, PostgreSQL, Redis, MinIO S3 Stream) are running normally.
                            </p>
                        </div>
                    </div>

                    <!-- TAB 2: LDAP DIRECTORY AUTHENTICATION CONFIG -->
                    <div id="adminTabLdap" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #f59e0b; margin:0 0 0.4rem 0;">🔐 LDAP Directory Authentication Setup</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Configure Enterprise LDAP / Active Directory single sign-on for admin and user logins</p>
                        </div>

                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem;">
                            <form onsubmit="handleSaveSystemSettings(event)">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">LDAP Authentication Status</label>
                                        <select id="toggleLdap" class="select-input" style="margin-bottom:0;">
                                            <option value="1">🟢 ENABLED (LDAP Directory Primary)</option>
                                            <option value="0">🔴 DISABLED (Local Database Only)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Use STARTTLS Encryption</label>
                                        <select id="cfgLdapUseTls" class="select-input" style="margin-bottom:0;">
                                            <option value="1">🟢 Yes (STARTTLS Enabled)</option>
                                            <option value="0">🔴 No (Plain TCP Connection)</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">LDAP Server Host</label>
                                        <input type="text" id="cfgLdapHost" class="select-input" placeholder="e.g. ldap.domain.com or 192.168.1.100" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">LDAP Server Port</label>
                                        <input type="number" id="cfgLdapPort" class="select-input" placeholder="389 or 636" style="margin-bottom:0;">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Base DN (Search Domain)</label>
                                        <input type="text" id="cfgLdapBaseDn" class="select-input" placeholder="e.g. dc=example,dc=com or ou=users,dc=domain,dc=local" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">User Lookup Attribute</label>
                                        <input type="text" id="cfgLdapUserAttr" class="select-input" placeholder="uid, sAMAccountName, or mail" style="margin-bottom:0;">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Bind Admin DN (System Account)</label>
                                        <input type="text" id="cfgLdapBindDn" class="select-input" placeholder="cn=admin,dc=example,dc=com" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Bind Admin Password</label>
                                        <input type="password" id="cfgLdapBindPass" class="select-input" placeholder="••••••••" style="margin-bottom:0;">
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <button type="button" class="back-btn" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border-color: #f59e0b;" onclick="handleTestLdap()">
                                        🔌 Test LDAP Server Connection
                                    </button>
                                    <button type="submit" class="action-btn" style="padding: 0.7rem 1.8rem; background: linear-gradient(135deg, #f59e0b, #ec4899);">
                                        💾 Save LDAP Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 3: SMTP EMAIL & GMAIL VERIFICATION CONFIG -->
                    <div id="adminTabSmtp" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #3b82f6; margin:0 0 0.4rem 0;">📧 SMTP Email & Gmail Verification Setup</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Configure Gmail App Password or custom SMTP server for mandatory email OTP registration</p>
                        </div>

                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem;">
                            <form onsubmit="handleSaveSystemSettings(event)">
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Require OTP Email Verification on Registration</label>
                                        <select id="toggleEmailVerification" class="select-input" style="margin-bottom:0;">
                                            <option value="1">🟢 ENABLED (Mandatory OTP Code via Email)</option>
                                            <option value="0">🔴 DISABLED (Direct Registration Without OTP)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">SMTP Host</label>
                                        <input type="text" id="cfgSmtpHost" class="select-input" placeholder="e.g. smtp.gmail.com or mail.yourdomain.com" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">SMTP Port</label>
                                        <input type="number" id="cfgSmtpPort" class="select-input" placeholder="587 (TLS) or 465 (SSL)" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">SMTP Username / Gmail Address</label>
                                        <input type="text" id="cfgSmtpUsername" class="select-input" placeholder="yourmail@gmail.com" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">SMTP Password / Gmail App Password</label>
                                        <input type="password" id="cfgSmtpPassword" class="select-input" placeholder="••••••••••••••••" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Sender Email Address (From)</label>
                                        <input type="text" id="cfgSmtpFromAddress" class="select-input" placeholder="no-reply@converter.bangden.my.id" style="margin-bottom:0;">
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.2rem;">
                                    <button type="button" class="back-btn" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border-color: #3b82f6;" onclick="handleTestEmail()">
                                        📩 Send Test Email to Admin
                                    </button>
                                    <button type="submit" class="action-btn" style="padding: 0.7rem 1.8rem; background: linear-gradient(135deg, #3b82f6, #06b6d4);">
                                        💾 Save SMTP & Email Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 4: PRICING & PAYMENT GATEWAYS CONFIG -->
                    <div id="adminTabPayment" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #06b6d4; margin:0 0 0.4rem 0;">⚙️ System, Pricing & Payment Gateways Config</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Manage subscription plan prices, discounts, promo codes, Midtrans, and WhatsApp Checkout</p>
                        </div>

                        <!-- 1. DYNAMIC PRICING & DISCOUNT MANAGER -->
                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1.1rem; color: #f59e0b; margin-bottom: 1rem;">🏷️ Dynamic Pricing & Discount Manager</h3>
                            <form onsubmit="handleSaveSystemSettings(event)">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                    <div style="background: rgba(30,41,59,0.5); padding: 1rem; border-radius: 12px;">
                                        <strong style="color: #06b6d4; display: block; margin-bottom: 0.5rem;">PRO PLAN CONFIG</strong>
                                        <label style="font-size: 0.78rem; color: var(--text-muted);">Base Price (Rp)</label>
                                        <input type="number" id="cfgProPrice" class="select-input" placeholder="49000" style="margin-bottom: 0.6rem;">
                                        
                                        <label style="font-size: 0.78rem; color: var(--text-muted);">Discount Percentage (%)</label>
                                        <input type="number" id="cfgProDiscountPercent" class="select-input" placeholder="20" style="margin-bottom: 0;">
                                    </div>

                                    <div style="background: rgba(30,41,59,0.5); padding: 1rem; border-radius: 12px;">
                                        <strong style="color: #ec4899; display: block; margin-bottom: 0.5rem;">ENTERPRISE PLAN CONFIG</strong>
                                        <label style="font-size: 0.78rem; color: var(--text-muted);">Base Price (Rp)</label>
                                        <input type="number" id="cfgEnterprisePrice" class="select-input" placeholder="149000" style="margin-bottom: 0.6rem;">
                                        
                                        <label style="font-size: 0.78rem; color: var(--text-muted);">Discount Percentage (%)</label>
                                        <input type="number" id="cfgEnterpriseDiscountPercent" class="select-input" placeholder="25" style="margin-bottom: 0;">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Special Promo Code String</label>
                                        <input type="text" id="cfgPromoCode" class="select-input" placeholder="DISCOUNT20" style="margin-bottom:0; text-transform: uppercase;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Promo Voucher Extra Discount (%)</label>
                                        <input type="number" id="cfgPromoDiscountPercent" class="select-input" placeholder="10" style="margin-bottom:0;">
                                    </div>
                                </div>

                                <!-- 2. PAYMENT GATEWAY KEYS & TOGGLES -->
                                <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.2rem; margin-top: 1rem;">
                                    <h3 style="font-size: 1.1rem; color: #a78bfa; margin-bottom: 1rem;">💳 Midtrans & WhatsApp Gateway Config</h3>
                                    
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">Midtrans Server Key</label>
                                            <input type="text" id="cfgMidtransServerKey" class="select-input" placeholder="Mid-server-..." style="margin-bottom:0;">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">Midtrans Client Key</label>
                                            <input type="text" id="cfgMidtransClientKey" class="select-input" placeholder="Mid-client-..." style="margin-bottom:0;">
                                        </div>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">WhatsApp Admin Number</label>
                                            <input type="text" id="cfgWaAdminNumber" class="select-input" placeholder="6282113237920" style="margin-bottom:0;">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">Midtrans Gateway Status</label>
                                            <select id="toggleMidtrans" class="select-input" style="margin-bottom:0;">
                                                <option value="1">🟢 ENABLED</option>
                                                <option value="0">🔴 DISABLED</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">Midtrans Mode</label>
                                            <select id="cfgMidtransMode" class="select-input" style="margin-bottom:0;">
                                                <option value="1">⚡ Production Mode</option>
                                                <option value="0">🧪 Sandbox Mode</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">WhatsApp Order Status</label>
                                            <select id="toggleWhatsApp" class="select-input" style="margin-bottom:0;">
                                                <option value="1">🟢 ENABLED</option>
                                                <option value="0">🔴 DISABLED</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="font-size: 0.8rem; color: var(--text-muted);">Sandbox Simulator Status</label>
                                            <select id="toggleSandboxSim" class="select-input" style="margin-bottom:0;">
                                                <option value="1">🟢 ENABLED</option>
                                                <option value="0">🔴 DISABLED</option>
                                            </select>
                                        </div>
                                    </div>

                                    <button type="submit" class="action-btn" style="padding: 0.7rem 1.5rem; font-size: 0.88rem; background: linear-gradient(135deg, #f59e0b, #ec4899);">💾 Save Pricing & Gateway Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 5: TELEGRAM BOT INTEGRATION CONFIG -->
                    <div id="adminTabTelegram" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #06b6d4; margin:0 0 0.4rem 0;">🤖 Telegram Bot Notifications Setup</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Configure real-time Telegram Bot alerts for new user registrations and sales</p>
                        </div>

                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem;">
                            <form onsubmit="handleSaveTelegramSettings(event)">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Telegram Bot Token</label>
                                        <input type="text" id="cfgTelegramBotToken" class="select-input" placeholder="e.g. 123456789:ABCdefGHIjklMNOpqrs" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Telegram Admin Chat ID</label>
                                        <input type="text" id="cfgTelegramChatId" class="select-input" placeholder="e.g. 987654321" style="margin-bottom:0;">
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.2rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Enable Instant Telegram Notifications</label>
                                        <select id="cfgEnableTelegramNotif" class="select-input" style="width: 200px; margin-bottom:0;">
                                            <option value="1">🟢 ENABLED</option>
                                            <option value="0">🔴 DISABLED</option>
                                        </select>
                                    </div>
                                    <div style="display: flex; gap: 0.8rem;">
                                        <button type="button" class="back-btn" style="background: rgba(6, 182, 212, 0.2); color: #06b6d4; border-color: #06b6d4;" onclick="handleTestTelegram()">🤖 Send Test Notification</button>
                                        <button type="submit" class="action-btn" style="padding: 0.7rem 1.5rem; font-size: 0.88rem;">💾 Save Telegram Config</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 6: ACTIVATION CODES GENERATOR -->
                    <div id="adminTabCodes" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #a78bfa; margin:0 0 0.4rem 0;">🔑 Activation Code Generator</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Generate and manage serial license codes for users</p>
                        </div>

                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem; margin-bottom: 2rem;">
                            <form onsubmit="handleAdminGenerateCodes(event)" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                                <div>
                                    <label style="font-size: 0.8rem; color: var(--text-muted);">Plan Type</label>
                                    <select id="genPlanType" class="select-input" style="margin-bottom:0;">
                                        <option value="pro">Pro Plan (Up to 50 files)</option>
                                        <option value="enterprise">Enterprise Plan (Unlimited)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem; color: var(--text-muted);">Duration (Days)</label>
                                    <input type="number" id="genDuration" class="select-input" value="30" style="margin-bottom:0;">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem; color: var(--text-muted);">Quantity</label>
                                    <input type="number" id="genQuantity" class="select-input" value="5" min="1" max="50" style="margin-bottom:0;">
                                </div>
                                <button type="submit" class="action-btn" style="padding: 0.7rem 1.5rem; font-size: 0.88rem;">✨ Generate Codes</button>
                            </form>
                        </div>

                        <div style="max-height: 480px; overflow-y: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Serial Code</th>
                                        <th>Plan Type</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody id="adminCodesTableBody">
                                    <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Loading codes...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 7: USER MANAGEMENT -->
                    <div id="adminTabUsers" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: #ec4899; margin:0 0 0.4rem 0;">👥 User Management & Roles</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin:0;">Manage registered users, roles, subscription plans, and accounts</p>
                        </div>

                        <div style="max-height: 480px; overflow-y: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User Name & Email</th>
                                        <th>Role</th>
                                        <th>Current Plan</th>
                                        <th>Plan Expires</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adminUsersTableBody">
                                    <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Loading users...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
