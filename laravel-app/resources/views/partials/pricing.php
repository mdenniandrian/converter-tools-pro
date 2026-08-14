        <!-- 4. PRICING & SUBSCRIPTION PLANS VIEW -->
        <div class="workspace-card" id="pricingCard" style="display: none;">
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <h2 style="font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #06b6d4, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    💎 Choose Your Perfect Conversion Plan
                </h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">
                    Unlock batch conversion limits, AI background removal, and high-speed processing
                </p>
            </div>

            <!-- PROMO CODE VOUCHER BAR -->
            <div style="max-width: 600px; margin: 0 auto 2.5rem auto; background: rgba(15,23,42,0.6); border: 1px dashed rgba(6, 182, 212, 0.4); border-radius: 16px; padding: 1.2rem; text-align: center;">
                <h4 style="font-size: 0.95rem; color: #06b6d4; margin-bottom: 0.5rem;">🎟️ Have a Promo Code or Voucher?</h4>
                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                    <input type="text" id="promoCodeInput" class="select-input" placeholder="e.g. DISCOUNT20" style="margin-bottom:0; max-width: 260px; text-transform: uppercase; text-align: center;">
                    <button class="action-btn" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;" onclick="applyPromoCode()">Apply Discount</button>
                </div>
                <div id="promoMessage" style="font-size: 0.8rem; margin-top: 0.5rem;"></div>
            </div>

            <div class="pricing-grid">
                
                <!-- FREE PLAN CARD -->
                <div class="pricing-card">
                    <h3 class="plan-title">FREE PLAN</h3>
                    <div class="plan-price">Rp 0 <span>/ forever</span></div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Ideal for occasional document conversion tasks</p>
                    
                    <ul class="plan-features">
                        <li><span>✓</span> Max 10 Files Batch Upload</li>
                        <li><span>✓</span> PDF & Document Converters (PDF ➔ Word/Excel)</li>
                        <li><span>✓</span> PDF & Document File Compressor</li>
                        <li style="color: var(--text-muted);"><span>✕</span> Image Converter (PRO Required)</li>
                        <li style="color: var(--text-muted);"><span>✕</span> AI Background Removal (ENTERPRISE Required)</li>
                    </ul>

                    <button class="action-btn" style="background: rgba(255,255,255,0.1); color: #fff;" disabled>Current Standard Plan</button>
                </div>

                <!-- PRO PLAN CARD -->
                <div class="pricing-card featured" id="proPlanCard">
                    <div style="position: absolute; top: -12px; right: 20px; background: linear-gradient(135deg, #06b6d4, #3b82f6); padding: 0.25rem 0.8rem; border-radius: 12px; font-size: 0.72rem; font-weight: 800; color: #fff;">
                        MOST POPULAR
                    </div>

                    <h3 class="plan-title" style="color: #06b6d4;">PRO PLAN</h3>
                    
                    <div class="plan-price">
                        <span id="proOriginalPrice" style="text-decoration: line-through; color: var(--text-muted); font-size: 1rem; margin-right: 0.4rem; display: <?= $sysSettings['pro_discount_percent'] > 0 ? 'inline' : 'none' ?>;">
                            Rp <?= number_format($sysSettings['pro_price'], 0, ',', '.') ?>
                        </span>
                        <span id="proDisplayPrice">Rp <?= number_format($sysSettings['pro_final_price'], 0, ',', '.') ?></span>
                        <span style="font-size: 0.85rem; font-weight: 500;">/ 30 Days</span>
                        
                        <?php if ($sysSettings['pro_discount_percent'] > 0): ?>
                            <span class="badge-discount" id="proDiscountBadge">🔥 <?= $sysSettings['pro_discount_percent'] ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">For professionals needing document & image conversion</p>
                    
                    <ul class="plan-features">
                        <li><span>✓</span> Max 50 Files Batch Upload</li>
                        <li><span>✓</span> PDF & Document Converters</li>
                        <li><span>✓</span> 🖼️ Image Format Converter (PNG, JPG, WEBP)</li>
                        <li><span>✓</span> High-Speed Server Processing</li>
                        <li style="color: var(--text-muted);"><span>✕</span> AI Background Removal (ENTERPRISE Required)</li>
                    </ul>

                    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                        <button class="action-btn btn-pay-midtrans" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);" onclick="payWithMidtrans('pro')">💳 Pay Instant via Midtrans (QRIS/VA)</button>
                        <button class="action-btn btn-pay-whatsapp" style="background: #25D366; color: #fff;" onclick="buyViaWhatsApp('PRO Plan')">💬 Buy via WhatsApp Admin</button>
                        <button class="action-btn btn-pay-sandbox" style="background: rgba(6, 182, 212, 0.15); border: 1px dashed #06b6d4;" onclick="simulateTestPayment('pro')">🧪 Simulate Test Payment (Sandbox Mode)</button>
                        <button class="action-btn" style="background: rgba(255,255,255,0.1); color: #fff;" onclick="openRedeemModal()">🔑 Redeem Code</button>
                    </div>
                </div>

                <!-- ENTERPRISE PLAN CARD -->
                <div class="pricing-card" id="enterprisePlanCard">
                    <h3 class="plan-title" style="color: #ec4899;">ENTERPRISE PLAN</h3>
                    
                    <div class="plan-price">
                        <span id="enterpriseOriginalPrice" style="text-decoration: line-through; color: var(--text-muted); font-size: 1rem; margin-right: 0.4rem; display: <?= $sysSettings['enterprise_discount_percent'] > 0 ? 'inline' : 'none' ?>;">
                            Rp <?= number_format($sysSettings['enterprise_price'], 0, ',', '.') ?>
                        </span>
                        <span id="enterpriseDisplayPrice">Rp <?= number_format($sysSettings['enterprise_final_price'], 0, ',', '.') ?></span>
                        <span style="font-size: 0.85rem; font-weight: 500;">/ Lifetime</span>

                        <?php if ($sysSettings['enterprise_discount_percent'] > 0): ?>
                            <span class="badge-discount" id="enterpriseDiscountBadge">🔥 <?= $sysSettings['enterprise_discount_percent'] ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Unlimited access for teams and power users</p>

                    <ul class="plan-features">
                        <li><span>✓</span> Unlimited Batch Files Upload</li>
                        <li><span>✓</span> All PDF & Document Converters</li>
                        <li><span>✓</span> 🖼️ Image Format Converter</li>
                        <li><span>✓</span> ✨ AI Background Remover (remove.bg)</li>
                        <li><span>✓</span> Priority Server Queue & Dedicated Support</li>
                    </ul>

                    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                        <button class="action-btn btn-pay-midtrans" style="background: linear-gradient(135deg, #ec4899, #8b5cf6);" onclick="payWithMidtrans('enterprise')">💳 Pay Instant via Midtrans (QRIS/VA)</button>
                        <button class="action-btn btn-pay-whatsapp" style="background: #25D366; color: #fff;" onclick="buyViaWhatsApp('ENTERPRISE Plan')">💬 Buy via WhatsApp Admin</button>
                        <button class="action-btn btn-pay-sandbox" style="background: rgba(236, 72, 153, 0.25); border: 1px dashed #f472b6;" onclick="simulateTestPayment('enterprise')">🧪 Simulate Test Payment (Sandbox Mode)</button>
                        <button class="action-btn" style="background: rgba(255,255,255,0.1); color: #fff;" onclick="openRedeemModal()">🔑 Redeem Code</button>
                    </div>
                </div>

            </div>
        </div>
