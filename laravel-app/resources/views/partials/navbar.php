    <!-- Navbar -->
    <nav class="navbar">
        <a href="#" class="logo" onclick="showToolsGrid()">⚡ Convertify Pro</a>
        <div class="nav-links">
            <button class="nav-item active" onclick="filterCategory('all', this)">All Tools</button>
            <button class="nav-item" onclick="filterCategory('doc', this)">PDF & Documents</button>
            <button class="nav-item" onclick="filterCategory('img', this)">Image Converter</button>
            <button class="nav-item" onclick="filterCategory('removebg', this)">✨ AI BG Remover</button>
            <button class="nav-item" onclick="filterCategory('compress', this)">🗜️ Compressor</button>
            <button class="nav-item" style="color: #06b6d4;" onclick="openPricingView()">💎 Pricing & Plans</button>

            <?php if ($authUser): ?>
                <span class="badge-plan plan-<?= strtolower($authUser['plan']) ?>"><?= strtoupper($authUser['plan']) ?></span>
                <button class="nav-item" style="color: #a78bfa;" onclick="openRedeemModal()">🔑 Redeem Code</button>
                <?php if ($authUser['role'] === 'admin'): ?>
                    <button class="nav-item" style="color: #ec4899;" onclick="openAdminView()">🏢 Backoffice</button>
                <?php endif; ?>
                <button class="nav-item" onclick="logoutUser()">Logout (<?= htmlspecialchars($authUser['name']) ?>)</button>
            <?php else: ?>
                <button class="nav-item" style="color: #a78bfa;" onclick="openAuthModal('login')">Login / Sign Up</button>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        
        <!-- Sub-Menu Category Selector -->
        <div class="sub-menu-bar" id="subMenuNav">
            <button class="sub-menu-pill active" onclick="filterCategory('all', this)">⚡ All Tools</button>
            <button class="sub-menu-pill" onclick="filterCategory('doc', this)">📄 PDF to Word</button>
            <button class="sub-menu-pill" onclick="filterCategory('doc', this)">📊 PDF to Excel</button>
            <button class="sub-menu-pill" onclick="filterCategory('doc', this)">📝 Word/Excel to PDF</button>
            <button class="sub-menu-pill" onclick="filterCategory('img', this)">🖼️ Image Format Convert</button>
            <button class="sub-menu-pill" onclick="selectTool('removebg')">✨ AI Remove Background</button>
            <button class="sub-menu-pill" onclick="selectTool('compress')">🗜️ File Compressor</button>
            <button class="sub-menu-pill" style="border-color: #06b6d4; color: #06b6d4;" onclick="openPricingView()">💎 View Pricing & Plans</button>
        </div>
