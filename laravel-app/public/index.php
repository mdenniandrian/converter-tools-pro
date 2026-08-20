<?php

// Require Autoloader / Manual Class Loader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Services\Database;
use App\Services\AuthService;
use App\Services\SettingsService;
use App\Services\TelegramService;
use App\Services\MidtransService;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AdminController;

AuthService::initSession();
$authUser = AuthService::getUser();
$sysSettings = SettingsService::getAll();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ----------------------------------------------------
// ROUTING API ENDPOINTS
// ----------------------------------------------------

// 1. PUBLIC API SETTINGS & PRICING
if ($uri === '/api/settings' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'settings' => SettingsService::getAll()]);
    exit;
}

// 2. AUTHENTICATION API (LOGIN / REGISTER / LOGOUT)
if ($uri === '/api/auth/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $res = AuthService::register($input['name'] ?? '', $input['email'] ?? '', $input['password'] ?? '');
    echo json_encode($res);
    exit;
}

if ($uri === '/api/auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $res = AuthService::login($input['email'] ?? '', $input['password'] ?? '');
    echo json_encode($res);
    exit;
}

if ($uri === '/api/auth/verify-email' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $res = AuthService::verifyEmail($input['email'] ?? '', $input['code'] ?? '');
    echo json_encode($res);
    exit;
}

if ($uri === '/api/auth/resend-code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $res = AuthService::resendCode($input['email'] ?? '');
    echo json_encode($res);
    exit;
}

if ($uri === '/api/auth/logout') {
    header('Content-Type: application/json');
    AuthService::logout();
    echo json_encode(['success' => true]);
    exit;
}

// 3. CONVERSION JOBS API
if ($uri === '/api/upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    JobController::handleUpload();
}

if (preg_match('#^/api/status/([a-zA-Z0-9\-]+)$#', $uri, $matches)) {
    JobController::handleStatus($matches[1]);
}

if (preg_match('#^/api/download/([a-zA-Z0-9\-]+)$#', $uri, $matches)) {
    JobController::handleDownload($matches[1]);
}

// 4. MIDTRANS PAYMENT GATEWAY & WEBHOOK
if ($uri === '/api/payment/checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!$authUser) {
        echo json_encode(['error' => 'Please login before proceeding to checkout']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $res = MidtransService::createCheckoutToken($authUser, $input['plan_type'] ?? 'pro');
    echo json_encode($res);
    exit;
}

if ($uri === '/api/payment/webhook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    MidtransService::handleWebhook($input);
    echo json_encode(['status' => 'OK']);
    exit;
}

// 5. ADMIN BACKOFFICE APIs
if ($uri === '/api/admin/data' && $_SERVER['REQUEST_METHOD'] === 'GET') AdminController::getData();
if ($uri === '/api/admin/generate-codes' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::generateCodes();
if ($uri === '/api/activate-code' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::redeemCode();
if ($uri === '/api/admin/user/plan' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::updateUserPlan();
if ($uri === '/api/admin/user/role' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::updateUserRole();
if ($uri === '/api/admin/user/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::deleteUser();
if ($uri === '/api/admin/settings' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::updateSettings();
if ($uri === '/api/admin/test-telegram' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::testTelegram();
if ($uri === '/api/payment/simulate' && $_SERVER['REQUEST_METHOD'] === 'POST') AdminController::simulatePayment();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertify Pro - All-in-One High Speed File Converter</title>
    <meta name="description" content="Enterprise grade file converter with PDF to Word, Excel, Image converter, and AI background remover.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: rgba(15, 23, 42, 0.75);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #06b6d4;
            --primary-hover: #0891b2;
            --accent: #3b82f6;
            --gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #8b5cf6 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', 'Inter', sans-serif; }
        body { background: var(--bg-dark); color: var(--text-main); min-height: 100vh; background-image: radial-gradient(circle at 50% 0%, rgba(6, 182, 212, 0.15) 0%, transparent 60%), radial-gradient(circle at 80% 50%, rgba(139, 92, 246, 0.1) 0%, transparent 50%); background-attachment: fixed; overflow-x: hidden; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

        /* NAVBAR */
        .navbar { display: flex; align-items: center; justify-content: space-between; padding: 1.2rem 2rem; border-bottom: 1px solid var(--border-color); background: rgba(9, 13, 22, 0.85); backdrop-filter: blur(16px); position: sticky; top: 0; z-index: 100; flex-wrap: wrap; gap: 0.8rem; }
        .logo { font-size: 1.5rem; font-weight: 800; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-decoration: none; }
        .nav-links { display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }
        .nav-item { background: transparent; border: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: 0.2s; padding: 0.5rem 0.8rem; border-radius: 8px; }
        .nav-item:hover, .nav-item.active { color: #fff; background: rgba(255, 255, 255, 0.05); }
        
        .sub-menu-bar { display: flex; gap: 0.6rem; overflow-x: auto; padding: 1rem 0; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); scrollbar-width: none; }
        .sub-menu-pill { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.45rem 1rem; border-radius: 20px; font-size: 0.82rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: 0.2s; }
        .sub-menu-pill:hover, .sub-menu-pill.active { background: rgba(6, 182, 212, 0.15); border-color: var(--primary); color: #fff; }

        /* GRID TOOLS */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; padding: 1.5rem 0 3rem 0; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.8rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); backdrop-filter: blur(12px); position: relative; overflow: hidden; }
        .card:hover { transform: translateY(-5px); border-color: rgba(6, 182, 212, 0.4); box-shadow: 0 12px 30px -10px rgba(6, 182, 212, 0.2); }
        .card-icon { font-size: 2.2rem; margin-bottom: 1rem; width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; border-radius: 14px; }
        .card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.4rem; color: #fff; }
        .card-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }

        /* WORKSPACE & DROPZONE */
        .workspace-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 2.5rem; backdrop-filter: blur(16px); margin: 1.5rem 0 3rem 0; }
        .dropzone { border: 2px dashed rgba(255, 255, 255, 0.2); border-radius: 18px; padding: 3rem 2rem; text-align: center; cursor: pointer; transition: 0.3s; background: rgba(255, 255, 255, 0.02); }
        .dropzone:hover, .dropzone.dragover { border-color: var(--primary); background: rgba(6, 182, 212, 0.05); }

        .select-input { width: 100%; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; padding: 0.8rem 1rem; border-radius: 12px; font-size: 0.95rem; margin-bottom: 1.2rem; outline: none; }
        .action-btn { background: var(--gradient); border: none; color: #fff; padding: 0.9rem 2rem; font-weight: 700; font-size: 0.95rem; border-radius: 12px; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3); }
        .action-btn:hover { opacity: 0.95; transform: scale(1.01); }
        .back-btn { background: rgba(255,255,255,0.08); border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; cursor: pointer; transition: 0.2s; }
        .back-btn:hover { color: #fff; background: rgba(255,255,255,0.15); }

        /* PRICING & DISCOUNTS */
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .pricing-card { background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-color); border-radius: 24px; padding: 2.2rem; backdrop-filter: blur(16px); position: relative; }
        .pricing-card.featured { border-color: var(--primary); box-shadow: 0 0 35px -5px rgba(6, 182, 212, 0.25); }
        .plan-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; }
        .plan-price { font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 1rem; }
        .plan-features { list-style: none; margin: 1.5rem 0 2rem 0; }
        .plan-features li { margin-bottom: 0.7rem; font-size: 0.88rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; }
        .plan-features li span { color: #10b981; font-weight: 700; }
        .badge-discount { background: linear-gradient(135deg, #ef4444, #f59e0b); color: #fff; font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 12px; margin-left: 0.4rem; vertical-align: middle; }

        /* REMOVE BG SPLIT PREVIEW */
        .removebg-preview-container { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
        .preview-box { background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-color); border-radius: 16px; padding: 1rem; text-align: center; }
        .preview-title { font-size: 0.82rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .preview-box img { max-width: 100%; max-height: 320px; border-radius: 12px; object-fit: contain; }
        .transparent-checkerboard { background-image: linear-gradient(45deg, #1e293b 25%, transparent 25%), linear-gradient(-45deg, #1e293b 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #1e293b 75%), linear-gradient(-45deg, transparent 75%, #1e293b 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; border-radius: 12px; min-height: 240px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }

        .file-badges-container { display: flex; flex-wrap: wrap; gap: 0.6rem; margin: 1rem 0; }
        .file-badge { background: rgba(30, 41, 59, 0.9); border: 1px solid rgba(255, 255, 255, 0.12); padding: 0.4rem 0.8rem; border-radius: 10px; font-size: 0.82rem; display: flex; align-items: center; gap: 0.5rem; }
        .file-badge-remove { color: #ef4444; font-weight: 800; cursor: pointer; }

        /* BACKOFFICE SIDEBAR */
        .admin-sidebar button.active { background: rgba(6, 182, 212, 0.2); color: var(--primary); border-color: var(--primary); font-weight: 700; }
        .admin-nav-item { background: transparent; border: 1px solid transparent; color: var(--text-muted); padding: 0.7rem 1rem; border-radius: 12px; font-size: 0.88rem; cursor: pointer; text-align: left; transition: 0.2s; display: flex; align-items: center; gap: 0.6rem; }
        .admin-nav-item:hover { color: #fff; background: rgba(255,255,255,0.05); }

        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
        .modal-card { background: #0f172a; border: 1px solid var(--border-color); border-radius: 24px; padding: 2rem; width: 100%; max-width: 440px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; }
        .badge-plan { padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 800; color: #fff; }
        .badge-plan.plan-free { background: #64748b; }
        .badge-plan.plan-pro { background: var(--primary); }
        .badge-plan.plan-enterprise { background: #ec4899; }
        .table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
        .table th, .table td { padding: 0.8rem 1rem; border-bottom: 1px solid var(--border-color); }
        .table th { color: var(--text-muted); font-weight: 600; }
    </style>

    <!-- Midtrans Snap JS Script -->
    <script type="text/javascript" src="<?= $sysSettings['midtrans_is_production'] ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>" data-client-key="<?= htmlspecialchars($sysSettings['midtrans_client_key']) ?>"></script>
</head>
<body>

    <?php require __DIR__ . '/../resources/views/partials/navbar.php'; ?>
    <?php require __DIR__ . '/../resources/views/partials/tools_grid.php'; ?>
    <?php require __DIR__ . '/../resources/views/partials/workspace.php'; ?>
    <?php require __DIR__ . '/../resources/views/partials/pricing.php'; ?>
    <?php require __DIR__ . '/../resources/views/partials/admin_backoffice.php'; ?>
    </div><!-- End .container -->

    <?php require __DIR__ . '/../resources/views/partials/modals.php'; ?>

    <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.8rem;">
        🔒 Encrypted & Stateless Processing • Files stream directly and are deleted from storage immediately after download.
    </div>

    <script>
        let authMode = 'login';
        let currentTool = 'pdf2word';
        let batchSelectedFiles = [];
        let pollTimers = {};
        let sysSettings = <?= json_encode($sysSettings ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?> || {};
        let authUser = <?= json_encode($authUser ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?> || null;

        /* CUSTOM NOTIFICATION TOAST & CONFIRM MODALS SYSTEM */
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.style.pointerEvents = 'auto';
            toast.style.padding = '0.95rem 1.2rem';
            toast.style.borderRadius = '14px';
            toast.style.fontSize = '0.9rem';
            toast.style.fontWeight = '600';
            toast.style.color = '#fff';
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '0.8rem';
            toast.style.backdropFilter = 'blur(16px)';
            toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.4)';
            toast.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';

            let icon = '✅';
            let bg = 'linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95))';
            let border = 'rgba(16, 185, 129, 0.4)';

            if (type === 'error') {
                icon = '❌';
                bg = 'linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95))';
                border = 'rgba(239, 68, 68, 0.4)';
            } else if (type === 'info') {
                icon = 'ℹ️';
                bg = 'linear-gradient(135deg, rgba(6, 182, 212, 0.95), rgba(59, 130, 246, 0.95))';
                border = 'rgba(6, 182, 212, 0.4)';
            }

            toast.style.background = bg;
            toast.style.border = `1px solid ${border}`;
            toast.innerHTML = `
                <span style="font-size: 1.2rem;">${icon}</span>
                <span style="flex: 1;">${message}</span>
                <span style="cursor: pointer; opacity: 0.7; font-size: 0.8rem;" onclick="this.parentElement.remove()">✕</span>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            });

            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }

        function showConfirm(options, onConfirm) {
            const modal = document.getElementById('confirmModal');
            if (!modal) {
                onConfirm();
                return;
            }

            const title = typeof options === 'string' ? 'Confirmation' : (options.title || 'Are you sure?');
            const message = typeof options === 'string' ? options : (options.message || '');
            const icon = options.icon || '⚠️';
            const confirmText = options.confirmText || 'Confirm';

            document.getElementById('confirmModalTitle').textContent = title;
            document.getElementById('confirmModalMessage').textContent = message;
            document.getElementById('confirmModalIcon').textContent = icon;

            const actionBtn = document.getElementById('confirmActionBtn');
            actionBtn.textContent = confirmText;
            if (options.confirmBtnStyle) actionBtn.style.cssText = `padding: 0.7rem 1.5rem; ${options.confirmBtnStyle}`;

            actionBtn.onclick = function() {
                closeModal('confirmModal');
                onConfirm();
            };

            modal.style.display = 'flex';
        }

        function applyPromoCode() {
            const input = document.getElementById('promoCodeInput').value.trim().toUpperCase();
            const msg = document.getElementById('promoMessage');
            if (input === sysSettings.promo_code) {
                const discount = sysSettings.promo_discount_percent;
                const proFinal = Math.round(sysSettings.pro_final_price * (1 - (discount / 100)));
                const entFinal = Math.round(sysSettings.enterprise_final_price * (1 - (discount / 100)));

                document.getElementById('proDisplayPrice').textContent = 'Rp ' + proFinal.toLocaleString('id-ID');
                document.getElementById('enterpriseDisplayPrice').textContent = 'Rp ' + entFinal.toLocaleString('id-ID');
                
                msg.style.color = '#10b981';
                msg.textContent = `🎉 Promo Code Applied! Extra ${discount}% discount activated.`;
                showToast(`Promo Code Applied! Extra ${discount}% discount activated.`, 'success');
            } else {
                msg.style.color = '#ef4444';
                msg.textContent = '❌ Invalid or expired promo code.';
                showToast('Invalid or expired promo code.', 'error');
            }
        }

        function buyViaWhatsApp(planName) {
            const userEmail = "<?= $authUser ? htmlspecialchars($authUser['email']) : 'Guest' ?>";
            const waNumber = sysSettings.wa_admin_number || "6282113237920";
            const text = encodeURIComponent(`Halo Admin Convertify Pro, saya bermaksud membeli Kode Lisensi Paket ${planName} untuk akun email: ${userEmail}`);
            window.open(`https://wa.me/${waNumber}?text=${text}`, '_blank');
        }

        function simulateTestPayment(planType) {
            showConfirm({
                title: '🧪 Test Payment Simulation',
                message: `Run Sandbox Test Payment Simulation for ${planType.toUpperCase()} plan?`,
                icon: '🧪',
                confirmText: 'Run Test Payment',
                confirmBtnStyle: 'background: linear-gradient(135deg, #06b6d4, #3b82f6);'
            }, async () => {
                try {
                    const res = await fetch('/api/payment/simulate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ plan_type: planType })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else showToast(data.error || "Simulation failed", 'error');
                } catch (err) { showToast("Error connecting to server", 'error'); }
            });
        }

        async function payWithMidtrans(planType) {
            try {
                const res = await fetch('/api/payment/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ plan_type: planType })
                });
                const data = await res.json();

                if (!data.success) {
                    showToast(data.error || "Failed to create checkout transaction", 'error');
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    showToast("Midtrans Snap JS not loaded. Check client key in Backoffice.", 'error');
                    return;
                }

                window.snap.pay(data.token, {
                    onSuccess: function(result) {
                        showToast("Payment successful! Account upgraded.", 'success');
                        setTimeout(() => location.reload(), 1200);
                    },
                    onPending: function(result) {
                        showToast("Payment pending. Please complete transaction via QRIS/VA.", 'info');
                    },
                    onError: function(result) {
                        showToast("Payment failed. Please try again.", 'error');
                    }
                });
            } catch (err) {
                showToast("Error connecting to Midtrans API gateway.", 'error');
            }
        }

        function filterCategory(cat, btn) {
            document.querySelectorAll('.sub-menu-pill').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            showToolsGrid();

            const cards = document.querySelectorAll('#toolsGrid .card');
            cards.forEach(card => {
                if (cat === 'all' || card.getAttribute('data-category') === cat) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openPricingView() {
            document.getElementById('toolsGrid').style.display = 'none';
            document.getElementById('workspaceCard').style.display = 'none';
            document.getElementById('adminCard').style.display = 'none';
            document.getElementById('pricingCard').style.display = 'block';
            
            if (!sysSettings.enable_midtrans) document.querySelectorAll('.btn-pay-midtrans').forEach(b => b.style.display = 'none');
            if (!sysSettings.enable_whatsapp) document.querySelectorAll('.btn-pay-whatsapp').forEach(b => b.style.display = 'none');
            if (!sysSettings.enable_sandbox_sim) document.querySelectorAll('.btn-pay-sandbox').forEach(b => b.style.display = 'none');
        }

        function showToolsGrid() {
            document.getElementById('workspaceCard').style.display = 'none';
            document.getElementById('adminCard').style.display = 'none';
            document.getElementById('pricingCard').style.display = 'none';
            document.getElementById('toolsGrid').style.display = 'grid';
        }

        function selectTool(toolKey) {
            const userPlan = (authUser && authUser.plan) ? authUser.plan.toLowerCase() : 'free';

            if (toolKey === 'imgconvert' && !['pro', 'enterprise'].includes(userPlan)) {
                if (document.getElementById('upgradeModalTitle')) document.getElementById('upgradeModalTitle').textContent = '⭐ PRO Plan Required';
                if (document.getElementById('upgradeModalMessage')) document.getElementById('upgradeModalMessage').innerHTML = 'Free plan is limited to <strong>PDF & Document</strong> tools only. Upgrade to <strong style="color: #06b6d4;">PRO</strong> or <strong style="color: #ec4899;">ENTERPRISE</strong> to unlock Image Format Converter (PNG, JPG, WEBP)!';
                document.getElementById('upgradeModal').style.display = 'flex';
                showToast("Image Converter requires a PRO or ENTERPRISE plan.", 'info');
                return;
            }

            if (toolKey === 'removebg' && userPlan !== 'enterprise') {
                if (document.getElementById('upgradeModalTitle')) document.getElementById('upgradeModalTitle').textContent = '👑 ENTERPRISE Plan Required';
                if (document.getElementById('upgradeModalMessage')) document.getElementById('upgradeModalMessage').innerHTML = 'AI Background Removal requires an <strong style="color: #ec4899;">ENTERPRISE</strong> plan. Upgrade now to unlock AI tools!';
                document.getElementById('upgradeModal').style.display = 'flex';
                showToast("AI Background Remover requires an ENTERPRISE plan.", 'info');
                return;
            }

            currentTool = toolKey;
            batchSelectedFiles = [];
            renderBatchFileBadges();

            document.getElementById('toolsGrid').style.display = 'none';
            document.getElementById('pricingCard').style.display = 'none';
            document.getElementById('adminCard').style.display = 'none';
            document.getElementById('workspaceCard').style.display = 'block';

            const title = document.getElementById('workspaceTitle');
            const wsRemoveBg = document.getElementById('wsRemoveBg');
            const wsBatch = document.getElementById('wsBatch');
            const formatGroup = document.getElementById('batchFormatGroup');
            const targetFormat = document.getElementById('batchTargetFormat');

            wsRemoveBg.style.display = 'none';
            wsBatch.style.display = 'none';

            if (toolKey === 'removebg') {
                title.textContent = '✨ AI Background Remover (remove.bg style)';
                wsRemoveBg.style.display = 'block';
            } else {
                wsBatch.style.display = 'block';
                if (toolKey === 'pdf2word') {
                    title.textContent = '📄 PDF to Word Converter';
                    formatGroup.style.display = 'block';
                    targetFormat.innerHTML = `<option value="docx">Word (.docx)</option>`;
                } else if (toolKey === 'pdf2excel') {
                    title.textContent = '📊 PDF to Excel Converter';
                    formatGroup.style.display = 'block';
                    targetFormat.innerHTML = `<option value="xlsx">Excel (.xlsx)</option>`;
                } else if (toolKey === 'doc2pdf') {
                    title.textContent = '📝 Word / Excel to PDF Converter';
                    formatGroup.style.display = 'block';
                    targetFormat.innerHTML = `<option value="pdf">PDF (.pdf)</option>`;
                } else if (toolKey === 'imgconvert') {
                    title.textContent = '🖼️ Image Format Converter';
                    formatGroup.style.display = 'block';
                    targetFormat.innerHTML = `
                        <option value="png">PNG (.png)</option>
                        <option value="jpg">JPG (.jpg)</option>
                        <option value="webp">WEBP (.webp)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    `;
                } else if (toolKey === 'compress') {
                    title.textContent = '🗜️ Document & Image File Compressor & Resizer';
                    formatGroup.style.display = 'block';
                    targetFormat.innerHTML = `
                        <option value="compress_mail">✉️ Standard Mail Compress (Printer/Mail - ~30% reduction, high print quality)</option>
                        <option value="compress_ebook">⚡ Medium Compress (eBook - ~60% size reduction)</option>
                        <option value="compress_max">🔥 Max Compress & Resize (Screen - 3MB ➔ ~400KB)</option>
                    `;
                }
            }
        }

        /* AUTH & MODALS */
        window.openAuthModal = function(mode) {
            authMode = mode || 'login';
            const m = document.getElementById('authModal');
            if (m) m.style.display = 'flex';
            const grp = document.getElementById('registerNameGroup');
            if (grp) grp.style.display = (authMode === 'register') ? 'block' : 'none';
            const title = document.getElementById('authModalTitle');
            if (title) title.textContent = (authMode === 'register') ? 'Register Account' : 'Login Account';
            const btn = document.getElementById('authSubmitBtn');
            if (btn) {
                btn.disabled = false;
                btn.textContent = (authMode === 'register') ? 'Create Account' : 'Login';
            }

            const tabLogin = document.getElementById('authTabLogin');
            const tabRegister = document.getElementById('authTabRegister');
            if (tabLogin && tabRegister) {
                if (authMode === 'register') {
                    tabRegister.classList.add('active');
                    tabLogin.classList.remove('active');
                } else {
                    tabLogin.classList.add('active');
                    tabRegister.classList.remove('active');
                }
            }

            setTimeout(() => {
                const targetInput = (authMode === 'register') ? document.getElementById('authName') : document.getElementById('authEmail');
                if (targetInput) targetInput.focus();
            }, 50);
        };

        window.toggleAuthMode = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            window.openAuthModal(authMode === 'login' ? 'register' : 'login');
        };

        window.closeModal = function(id) {
            const m = document.getElementById(id);
            if (m) m.style.display = 'none';
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
            }
        });

        window.openRedeemModal = function() {
            const m = document.getElementById('redeemModal');
            if (m) m.style.display = 'flex';
        };

        let currentVerifyEmail = '';

        async function handleAuthSubmit(e) {
            e.preventDefault();
            const emailVal = (document.getElementById('authEmail').value || '').trim();
            const passVal = (document.getElementById('authPassword').value || '').trim();
            const nameVal = (document.getElementById('authName') ? document.getElementById('authName').value : '').trim();

            if (authMode === 'register' && !nameVal) {
                showToast("Mohon masukkan nama lengkap Anda.", 'info');
                if (document.getElementById('authName')) document.getElementById('authName').focus();
                return;
            }

            if (!emailVal) {
                showToast("Mohon masukkan alamat email Anda.", 'info');
                document.getElementById('authEmail').focus();
                return;
            }

            if (!passVal || passVal.length < 6) {
                showToast("Password minimal 6 karakter.", 'info');
                document.getElementById('authPassword').focus();
                return;
            }

            const endpoint = authMode === 'register' ? '/api/auth/register' : '/api/auth/login';
            const btn = document.getElementById('authSubmitBtn');
            const origText = btn ? btn.textContent : 'Submit';
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Memproses...';
            }

            const body = {
                name: nameVal,
                email: emailVal,
                password: passVal
            };

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = origText;
                }

                if (data.requires_verification) {
                    currentVerifyEmail = data.email || emailVal;
                    closeModal('authModal');
                    openOtpModal(currentVerifyEmail, data.message || data.error);
                } else if (data.success) {
                    showToast(data.message || "Authentication successful! Welcome.", 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.error || "Authentication failed", 'error');
                }
            } catch (err) {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = origText;
                }
                showToast("Error connecting to server", 'error');
            }
        }
        window.handleAuthSubmit = handleAuthSubmit;

        function openOtpModal(email, msg) {
            currentVerifyEmail = email;
            if (document.getElementById('otpEmailSpan')) document.getElementById('otpEmailSpan').textContent = email;
            if (msg) showToast(msg, 'info');
            document.getElementById('otpModal').style.display = 'flex';
        }

        async function handleVerifyOtpSubmit(e) {
            e.preventDefault();
            const code = document.getElementById('otpCodeInput').value;
            try {
                const res = await fetch('/api/auth/verify-email', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: currentVerifyEmail, code })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message || "Email verified successfully!", 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || "Verification failed", 'error');
                }
            } catch (err) { showToast("Error connecting to server", 'error'); }
        }

        async function handleResendOtp() {
            try {
                const res = await fetch('/api/auth/resend-code', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: currentVerifyEmail })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.error || "Failed to resend OTP", 'error');
                }
            } catch (err) { showToast("Error connecting to server", 'error'); }
        }

        async function logoutUser() {
            await fetch('/api/auth/logout');
            showToast("Logged out successfully.", 'info');
            setTimeout(() => location.reload(), 800);
        }

        async function handleRedeemCode(e) {
            e.preventDefault();
            const code = document.getElementById('redeemCodeInput').value;
            try {
                const res = await fetch('/api/activate-code', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else showToast(data.error || "Failed to redeem code", 'error');
            } catch (err) { showToast("Error connecting to server", 'error'); }
        }

        /* BACKOFFICE ADMIN SIDEBAR NAVIGATION */
        function switchAdminTab(tabName, btn) {
            document.querySelectorAll('.admin-tab-content').forEach(t => t.style.display = 'none');
            document.querySelectorAll('.admin-nav-item').forEach(b => b.classList.remove('active'));
            
            const target = document.getElementById('adminTab' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
            if (target) target.style.display = 'block';
            if (btn) btn.classList.add('active');
        }

        async function handleSaveSystemSettings(e) {
            e.preventDefault();
            const body = {
                pro_price: document.getElementById('cfgProPrice').value,
                pro_discount_percent: document.getElementById('cfgProDiscountPercent').value,
                enterprise_price: document.getElementById('cfgEnterprisePrice').value,
                enterprise_discount_percent: document.getElementById('cfgEnterpriseDiscountPercent').value,
                promo_code: document.getElementById('cfgPromoCode').value,
                promo_discount_percent: document.getElementById('cfgPromoDiscountPercent').value,
                midtrans_server_key: document.getElementById('cfgMidtransServerKey').value,
                midtrans_client_key: document.getElementById('cfgMidtransClientKey').value,
                wa_admin_number: document.getElementById('cfgWaAdminNumber').value,
                enable_midtrans: document.getElementById('toggleMidtrans').value === '1',
                midtrans_is_production: document.getElementById('cfgMidtransMode').value === '1',
                enable_whatsapp: document.getElementById('toggleWhatsApp').value === '1',
                enable_sandbox_sim: document.getElementById('toggleSandboxSim').value === '1',
                enable_email_verification: document.getElementById('toggleEmailVerification') ? document.getElementById('toggleEmailVerification').value === '1' : true,
                smtp_host: document.getElementById('cfgSmtpHost') ? document.getElementById('cfgSmtpHost').value : '',
                smtp_port: document.getElementById('cfgSmtpPort') ? document.getElementById('cfgSmtpPort').value : '587',
                smtp_username: document.getElementById('cfgSmtpUsername') ? document.getElementById('cfgSmtpUsername').value : '',
                smtp_password: document.getElementById('cfgSmtpPassword') ? document.getElementById('cfgSmtpPassword').value : '',
                smtp_from_address: document.getElementById('cfgSmtpFromAddress') ? document.getElementById('cfgSmtpFromAddress').value : ''
            };

            try {
                const res = await fetch('/api/admin/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (data.success) showToast(data.message, 'success');
                else showToast(data.error || "Failed to save settings", 'error');
            } catch (err) { showToast("Error connecting to server", 'error'); }
        }

        async function handleSaveTelegramSettings(e) {
            e.preventDefault();
            const body = {
                telegram_bot_token: document.getElementById('cfgTelegramBotToken').value,
                telegram_chat_id: document.getElementById('cfgTelegramChatId').value,
                enable_telegram_notif: document.getElementById('cfgEnableTelegramNotif').value === '1'
            };

            try {
                const res = await fetch('/api/admin/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (data.success) showToast(data.message, 'success');
                else showToast(data.error || "Failed to save Telegram settings", 'error');
            } catch (err) { showToast("Error connecting to server", 'error'); }
        }

        async function handleTestTelegram() {
            try {
                const res = await fetch('/api/admin/test-telegram', { method: 'POST' });
                const data = await res.json();
                if (data.success) showToast(data.message, 'success');
                else showToast(data.error || "Failed to send test notification", 'error');
            } catch (err) { showToast("Error sending Telegram test", 'error'); }
        }

        /* BACKOFFICE ADMIN VIEW */
        async function openAdminView() {
            document.getElementById('toolsGrid').style.display = 'none';
            document.getElementById('workspaceCard').style.display = 'none';
            document.getElementById('pricingCard').style.display = 'none';
            document.getElementById('adminCard').style.display = 'block';

            try {
                const res = await fetch('/api/admin/data');
                const data = await res.json();

                if (data.settings) {
                    const s = data.settings;
                    sysSettings = s;
                    document.getElementById('toggleMidtrans').value = s.enable_midtrans ? '1' : '0';
                    document.getElementById('toggleWhatsApp').value = s.enable_whatsapp ? '1' : '0';
                    document.getElementById('toggleSandboxSim').value = s.enable_sandbox_sim ? '1' : '0';
                    
                    if (document.getElementById('cfgProPrice')) document.getElementById('cfgProPrice').value = s.pro_price || 49000;
                    if (document.getElementById('cfgProDiscountPercent')) document.getElementById('cfgProDiscountPercent').value = s.pro_discount_percent || 20;
                    if (document.getElementById('cfgEnterprisePrice')) document.getElementById('cfgEnterprisePrice').value = s.enterprise_price || 149000;
                    if (document.getElementById('cfgEnterpriseDiscountPercent')) document.getElementById('cfgEnterpriseDiscountPercent').value = s.enterprise_discount_percent || 25;
                    if (document.getElementById('cfgPromoCode')) document.getElementById('cfgPromoCode').value = s.promo_code || 'DISCOUNT20';
                    if (document.getElementById('cfgPromoDiscountPercent')) document.getElementById('cfgPromoDiscountPercent').value = s.promo_discount_percent || 10;
                    
                    if (document.getElementById('cfgMidtransServerKey')) document.getElementById('cfgMidtransServerKey').value = s.midtrans_server_key || '';
                    if (document.getElementById('cfgMidtransClientKey')) document.getElementById('cfgMidtransClientKey').value = s.midtrans_client_key || '';
                    if (document.getElementById('cfgWaAdminNumber')) document.getElementById('cfgWaAdminNumber').value = s.wa_admin_number || '';
                    if (document.getElementById('cfgMidtransMode')) document.getElementById('cfgMidtransMode').value = s.midtrans_is_production ? '1' : '0';
                    if (document.getElementById('cfgTelegramBotToken')) document.getElementById('cfgTelegramBotToken').value = s.telegram_bot_token || '';
                    if (document.getElementById('cfgTelegramChatId')) document.getElementById('cfgTelegramChatId').value = s.telegram_chat_id || '';
                    if (document.getElementById('cfgEnableTelegramNotif')) document.getElementById('cfgEnableTelegramNotif').value = s.enable_telegram_notif ? '1' : '0';

                    if (document.getElementById('toggleEmailVerification')) document.getElementById('toggleEmailVerification').value = s.enable_email_verification ? '1' : '0';
                    if (document.getElementById('cfgSmtpHost')) document.getElementById('cfgSmtpHost').value = s.smtp_host || '';
                    if (document.getElementById('cfgSmtpPort')) document.getElementById('cfgSmtpPort').value = s.smtp_port || 587;
                    if (document.getElementById('cfgSmtpUsername')) document.getElementById('cfgSmtpUsername').value = s.smtp_username || '';
                    if (document.getElementById('cfgSmtpPassword')) document.getElementById('cfgSmtpPassword').value = s.smtp_password || '';
                    if (document.getElementById('cfgSmtpFromAddress')) document.getElementById('cfgSmtpFromAddress').value = s.smtp_from_address || 'no-reply@converter.bangden.my.id';
                }

                if (data.stats) {
                    document.getElementById('statTotalJobs').textContent = data.stats.total_jobs;
                    document.getElementById('statTotalUsers').textContent = data.stats.total_users;
                    document.getElementById('statTotalCodes').textContent = data.stats.total_codes;
                }

                renderAdminCodesTable(data.codes || []);
                renderAdminUsersTable(data.users || []);

            } catch (err) { showToast("Error loading admin backoffice data", 'error'); }
        }

        function renderAdminCodesTable(codes) {
            const tbody = document.getElementById('adminCodesTableBody');
            if (codes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No activation codes generated yet.</td></tr>`;
                return;
            }
            tbody.innerHTML = codes.map(c => `
                <tr>
                    <td><strong style="color:#a78bfa; letter-spacing:1px;">${c.code}</strong></td>
                    <td><span class="badge-plan plan-${c.plan_type}">${c.plan_type.toUpperCase()}</span></td>
                    <td>${c.duration_days} Days</td>
                    <td>${c.is_active ? '🟢 ACTIVE' : '🔴 USED'}</td>
                    <td style="color: var(--text-muted); font-size: 0.78rem;">${c.created_at}</td>
                </tr>
            `).join('');
        }

        function renderAdminUsersTable(users) {
            const tbody = document.getElementById('adminUsersTableBody');
            tbody.innerHTML = users.map(u => `
                <tr>
                    <td>
                        <strong>${u.name}</strong><br>
                        <span style="color: var(--text-muted); font-size: 0.78rem;">${u.email}</span>
                    </td>
                    <td>
                        <select onchange="updateUserRole('${u.id}', this.value)" style="background:#0f172a; color:#fff; border:1px solid rgba(255,255,255,0.2); border-radius:6px; padding:0.2rem 0.4rem; font-size:0.8rem;">
                            <option value="user" ${u.role === 'user' ? 'selected' : ''}>USER</option>
                            <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>ADMIN</option>
                        </select>
                    </td>
                    <td>
                        <select onchange="updateUserPlan('${u.id}', this.value)" style="background:#0f172a; color:#fff; border:1px solid rgba(255,255,255,0.2); border-radius:6px; padding:0.2rem 0.4rem; font-size:0.8rem;">
                            <option value="free" ${u.plan === 'free' ? 'selected' : ''}>FREE</option>
                            <option value="pro" ${u.plan === 'pro' ? 'selected' : ''}>PRO</option>
                            <option value="enterprise" ${u.plan === 'enterprise' ? 'selected' : ''}>ENTERPRISE</option>
                        </select>
                    </td>
                    <td style="font-size:0.78rem; color: var(--text-muted);">${u.plan_expires_at || 'Lifetime / N/A'}</td>
                    <td>
                        <button class="back-btn" style="background: rgba(239,68,68,0.2); color:#ef4444; border-color:#ef4444; padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="deleteUserAccount('${u.id}')">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        async function handleAdminGenerateCodes(e) {
            e.preventDefault();
            const body = {
                plan_type: document.getElementById('genPlanType').value,
                duration_days: document.getElementById('genDuration').value,
                quantity: document.getElementById('genQuantity').value
            };
            try {
                const res = await fetch('/api/admin/generate-codes', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    openAdminView();
                } else showToast(data.error || "Failed to generate codes", 'error');
            } catch (err) { showToast("Error connecting to server", 'error'); }
        }

        async function updateUserPlan(userId, plan) {
            try {
                const res = await fetch('/api/admin/user/plan', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, plan: plan, duration_days: 30 })
                });
                const data = await res.json();
                if (data.success) showToast(data.message, 'success');
                else showToast(data.error, 'error');
            } catch (err) { showToast("Error updating user plan", 'error'); }
        }

        async function updateUserRole(userId, role) {
            try {
                const res = await fetch('/api/admin/user/role', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, role: role })
                });
                const data = await res.json();
                if (data.success) showToast(data.message, 'success');
                else showToast(data.error, 'error');
            } catch (err) { showToast("Error updating user role", 'error'); }
        }

        function deleteUserAccount(userId) {
            showConfirm({
                title: '🗑️ Delete User Account',
                message: 'Are you sure you want to permanently delete this user account? This action cannot be undone.',
                icon: '🗑️',
                confirmText: 'Delete User',
                confirmBtnStyle: 'background: linear-gradient(135deg, #ef4444, #dc2626);'
            }, async () => {
                try {
                    const res = await fetch('/api/admin/user/delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_id: userId })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message, 'success');
                        openAdminView();
                    } else showToast(data.error, 'error');
                } catch (err) { showToast("Error deleting user account", 'error'); }
            });
        }

        /* BATCH UPLOAD HANDLERS */
        function handleBatchFilesSelect(files) {
            for (let i = 0; i < files.length; i++) {
                batchSelectedFiles.push(files[i]);
            }
            renderBatchFileBadges();
        }

        function removeBatchFile(index) {
            batchSelectedFiles.splice(index, 1);
            renderBatchFileBadges();
        }

        function renderBatchFileBadges() {
            const container = document.getElementById('batchFileBadges');
            if (batchSelectedFiles.length === 0) {
                container.innerHTML = '';
                return;
            }
            container.innerHTML = batchSelectedFiles.map((f, idx) => `
                <div class="file-badge">
                    <span>📄 ${f.name} (${(f.size / (1024*1024)).toFixed(2)} MB)</span>
                    <span class="file-badge-remove" onclick="removeBatchFile(${idx})">✕</span>
                </div>
            `).join('');
        }

        async function handleBatchSubmit(e) {
            e.preventDefault();
            if (batchSelectedFiles.length === 0) {
                showToast("Please select at least one file to convert.", 'info');
                return;
            }

            const formData = new FormData();
            batchSelectedFiles.forEach(f => formData.append('files[]', f));
            formData.append('target_format', document.getElementById('batchTargetFormat').value);

            const btn = document.getElementById('batchSubmitBtn');
            btn.disabled = true;
            btn.textContent = 'Uploading & Processing...';

            try {
                const res = await fetch('/api/upload', { method: 'POST', body: formData });
                const data = await res.json();

                if (!res.ok || data.error) {
                    if (data.code === 'UPGRADE_REQUIRED') openAuthModal('register');
                    showToast(data.error || "Upload failed", 'error');
                    btn.disabled = false;
                    btn.textContent = 'Start Conversion';
                    return;
                }

                batchSelectedFiles = [];
                renderBatchFileBadges();

                renderJobCards(data.jobs);
                data.jobs.forEach(job => pollJobStatus(job.job_id));

                btn.disabled = false;
                btn.textContent = 'Start Conversion';
                showToast(`${data.jobs.length} file(s) queued for processing!`, 'success');

            } catch (err) {
                showToast("Error submitting files for conversion", 'error');
                btn.disabled = false;
                btn.textContent = 'Start Conversion';
            }
        }

        function renderJobCards(jobs) {
            const container = document.getElementById('batchJobCards');
            container.innerHTML = jobs.map(j => `
                <div class="card" style="margin-bottom: 1rem; cursor: default;" id="jobCard_${j.job_id}">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #fff; font-size: 0.95rem;">📄 ${j.original_filename}</strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.2rem;">Target: ${j.target_format.toUpperCase()}</span>
                        </div>
                        <div id="jobStatusBox_${j.job_id}">
                            <span style="background: rgba(245,158,11,0.2); color: #f59e0b; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">⏳ Processing...</span>
                        </div>
                    </div>
                </div>
            `).join('') + container.innerHTML;
        }

        function pollJobStatus(jobId) {
            pollTimers[jobId] = setInterval(async () => {
                try {
                    const res = await fetch('/api/status/' + jobId);
                    const data = await res.json();

                    if (data.status === 'done') {
                        clearInterval(pollTimers[jobId]);
                        document.getElementById('jobStatusBox_' + jobId).innerHTML = `
                            <a href="/api/download/${jobId}" onclick="triggerDownloadAndRemove('${jobId}')" class="action-btn" style="padding: 0.4rem 1rem; font-size: 0.8rem; text-decoration: none;">⬇️ Download File</a>
                        `;
                        showToast("Conversion completed! Click Download.", 'success');
                    } else if (data.status === 'failed') {
                        clearInterval(pollTimers[jobId]);
                        document.getElementById('jobStatusBox_' + jobId).innerHTML = `
                            <span style="background: rgba(239,68,68,0.2); color: #ef4444; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">❌ Conversion Failed</span>
                        `;
                        showToast("File conversion failed.", 'error');
                    }
                } catch (e) {}
            }, 2000);
        }

        function triggerDownloadAndRemove(jobId) {
            showToast("Downloading file...", 'success');
            setTimeout(() => {
                const card = document.getElementById('jobCard_' + jobId);
                if (card) {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';
                    setTimeout(() => card.remove(), 400);
                }
            }, 800);
        }

        /* REMOVE BG PREVIEW HANDLER */
        async function handleRemoveBgFileSelect(files) {
            if (!files || files.length === 0) return;
            const file = files[0];

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imgPreviewOriginal').src = e.target.result;
                document.getElementById('removeBgPreviewContainer').style.display = 'grid';
                document.getElementById('removeBgSpinner').style.display = 'block';
                document.getElementById('imgPreviewResult').style.display = 'none';
                document.getElementById('removeBgPlaceholderText').textContent = 'Processing image background removal...';
            };
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append('file', file);
            formData.append('target_format', 'removebg');

            try {
                const res = await fetch('/api/upload', { method: 'POST', body: formData });
                const data = await res.json();

                if (!res.ok || data.error) {
                    showToast(data.error || "Background removal failed", 'error');
                    return;
                }

                const jobId = data.jobs[0].job_id;
                pollRemoveBgJob(jobId);
            } catch (err) { showToast("Error removing background", 'error'); }
        }

        function pollRemoveBgJob(jobId) {
            const timer = setInterval(async () => {
                try {
                    const res = await fetch('/api/status/' + jobId);
                    const data = await res.json();

                    if (data.status === 'done') {
                        clearInterval(timer);
                        document.getElementById('removeBgSpinner').style.display = 'none';
                        document.getElementById('removeBgPlaceholderText').style.display = 'none';

                        const resultImg = document.getElementById('imgPreviewResult');
                        resultImg.src = '/api/download/' + jobId;
                        resultImg.style.display = 'block';

                        const btnDownload = document.getElementById('btnDownloadRemoveBg');
                        btnDownload.style.display = 'inline-block';
                        btnDownload.onclick = () => window.location.href = '/api/download/' + jobId;

                        showToast("Background removal complete!", 'success');
                    } else if (data.status === 'failed') {
                        clearInterval(timer);
                        document.getElementById('removeBgSpinner').style.display = 'none';
                        document.getElementById('removeBgPlaceholderText').textContent = '❌ Background removal failed.';
                        showToast("Background removal failed.", 'error');
                    }
                } catch (e) {}
            }, 1500);
        }
    </script>
</body>
</html>
