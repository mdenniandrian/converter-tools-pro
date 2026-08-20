        <!-- 3. ADMIN BACKOFFICE VIEW (SIDEBAR DASHBOARD LAYOUT) -->
        <div class="workspace-card" id="adminCard" style="display: none; padding: 0; overflow: hidden;">
            <div style="display: flex; min-height: 620px;">
                
                <!-- SIDEBAR NAVIGATION -->
                <div class="admin-sidebar" style="width: 240px; background: rgba(15, 23, 42, 0.95); border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 1.5rem 1rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; padding-bottom: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 1.5rem;">
                            <span style="font-size: 1.5rem;">🏢</span>
                            <div>
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: #fff;">Backoffice</h3>
                                <span style="font-size: 0.72rem; color: var(--text-muted);">Admin Control Panel</span>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.4rem;" id="adminSidebarNav">
                            <button class="admin-nav-item active" onclick="switchAdminTab('overview', this)">
                                <span>📊</span> Overview & Stats
                            </button>
                            <button class="admin-nav-item" onclick="switchAdminTab('payment', this)">
                                <span>⚙️</span> System & Pricing
                            </button>
                            <button class="admin-nav-item" onclick="switchAdminTab('codes', this)">
                                <span>🔑</span> Activation Codes
                            </button>
                            <button class="admin-nav-item" onclick="switchAdminTab('users', this)">
                                <span>👥</span> User Management
                            </button>
                        </div>
                    </div>

                    <button class="back-btn" style="width: 100%; text-align: center; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" onclick="showToolsGrid()">← Exit Backoffice</button>
                </div>

                <!-- RIGHT MAIN CONTENT WORKSPACE -->
                <div style="flex: 1; padding: 2rem; overflow-y: auto; max-height: 680px;">
                    
                    <!-- TAB 1: OVERVIEW & STATS -->
                    <div id="adminTabOverview" class="admin-tab-content">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; color: #3b82f6;">📊 System Analytics & Overview</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem;">Real-time metrics of processed files, active users, and serial activation codes</p>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; margin-bottom: 2rem;">
                            <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Total Files Converted</span>
                                <div style="font-size: 2rem; font-weight: 800; color: #06b6d4; margin-top: 0.3rem;" id="statTotalJobs">0</div>
                            </div>
                            <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Registered Users</span>
                                <div style="font-size: 2rem; font-weight: 800; color: #a78bfa; margin-top: 0.3rem;" id="statTotalUsers">0</div>
                            </div>
                            <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Active Activation Codes</span>
                                <div style="font-size: 2rem; font-weight: 800; color: #ec4899; margin-top: 0.3rem;" id="statTotalCodes">0</div>
                            </div>
                        </div>

                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem;">
                            <h3 style="font-size: 1.1rem; color: #fff; margin-bottom: 0.8rem;">⚡ Microservices Health Status</h3>
                            <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6;">
                                🟢 <strong style="color: #10b981;">ALL SYSTEMS OPERATIONAL</strong><br>
                                All microservices (Golang LibreOffice Worker, Python AI BG Worker, PostgreSQL, Redis, MinIO S3 Stream) are running normally.
                            </p>
                        </div>
                    </div>

                    <!-- TAB 2: SYSTEM, PAYMENT & PRICING CONFIG -->
                    <div id="adminTabPayment" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; color: #06b6d4;">⚙️ System, Pricing & Integrations Config</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem;">Manage prices, discounts, promo codes, payment gateways, and Telegram Bot</p>
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
                            </form>
                        </div>

                        <!-- 3. TELEGRAM BOT INTEGRATION CARD -->
                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h3 style="font-size: 1.1rem; color: #06b6d4;">🤖 Telegram Bot Instant Notifications</h3>
                                <button type="button" class="back-btn" style="background: rgba(6, 182, 212, 0.2); color: #06b6d4; border-color: #06b6d4;" onclick="handleTestTelegram()">🤖 Send Test Notification</button>
                            </div>

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

                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Enable Instant Telegram Notifications</label>
                                        <select id="cfgEnableTelegramNotif" class="select-input" style="width: 200px; margin-bottom:0;">
                                            <option value="1">🟢 ENABLED</option>
                                            <option value="0">🔴 DISABLED</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="action-btn" style="padding: 0.7rem 1.5rem; font-size: 0.88rem;">💾 Save Telegram Config</button>
                                </div>
                            </form>
                        <!-- 3.5 SMTP & EMAIL VERIFICATION CONFIGURATION -->
                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1.1rem; color: #3b82f6; margin-bottom: 1rem;">📧 SMTP Email & OTP Verification Settings</h3>
                            <form onsubmit="handleSaveSystemSettings(event)">
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Require OTP Email Verification on Registration</label>
                                        <select id="toggleEmailVerification" class="select-input" style="margin-bottom:0;">
                                            <option value="1">🟢 ENABLED (Mandatory OTP Code)</option>
                                            <option value="0">🔴 DISABLED (Direct Register)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">SMTP Host</label>
                                        <input type="text" id="cfgSmtpHost" class="select-input" placeholder="e.g. smtp.gmail.com or mail.yourdomain.com" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">SMTP Port</label>
                                        <input type="number" id="cfgSmtpPort" class="select-input" placeholder="587 or 465" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">SMTP Username / Email</label>
                                        <input type="text" id="cfgSmtpUsername" class="select-input" placeholder="user@gmail.com" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">SMTP Password / App Password</label>
                                        <input type="password" id="cfgSmtpPassword" class="select-input" placeholder="••••••••" style="margin-bottom:0;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.8rem; color: var(--text-muted);">Sender Email Address (From)</label>
                                        <input type="text" id="cfgSmtpFromAddress" class="select-input" placeholder="no-reply@converter.bangden.my.id" style="margin-bottom:0;">
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <button type="submit" class="action-btn" style="padding: 0.7rem 1.5rem; font-size: 0.88rem; background: linear-gradient(135deg, #3b82f6, #06b6d4);">💾 Save SMTP & Email Settings</button>
                                </div>
                            </form>
                        </div>

                        <!-- 4. DATABASE & STORAGE CONNECTION VISUALIZER -->
                        <div style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 1.5rem;">
                            <h3 style="font-size: 1.1rem; color: #10b981; margin-bottom: 1rem;">🗄️ Database & Storage Connections Status</h3>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1rem;">
                                    <strong style="font-size: 0.88rem; color: #fff; display: block; margin-bottom: 0.3rem;">🐘 PostgreSQL DB</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Host: <code>postgres:5432</code></span><br>
                                    <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">● CONNECTED & ACTIVE</span>
                                </div>
                                <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1rem;">
                                    <strong style="font-size: 0.88rem; color: #fff; display: block; margin-bottom: 0.3rem;">⚡ Redis Cache & Queue</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Host: <code>redis:6379</code></span><br>
                                    <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">● CONNECTED & HEALTHY</span>
                                </div>
                                <div style="background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1rem;">
                                    <strong style="font-size: 0.88rem; color: #fff; display: block; margin-bottom: 0.3rem;">🪣 MinIO S3 Object Storage</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Bucket: <code>temp-converter-files</code></span><br>
                                    <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">● STREAM ENCRYPTED</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 3: ACTIVATION CODES -->
                    <div id="adminTabCodes" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; color: #a78bfa;">🔑 Activation Code Generator</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem;">Generate and manage serial license codes for users</p>
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

                    <!-- TAB 4: USER MANAGEMENT -->
                    <div id="adminTabUsers" class="admin-tab-content" style="display: none;">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.4rem; color: #ec4899;">👥 User Management</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem;">Manage registered users, roles, subscription plans, and accounts</p>
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
