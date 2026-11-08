<?php
$navItems = [
    'lookbook' => ['Lookbook Khách Hàng', '/index.php?module=lookbook&tab=lookbook'],
    'cart'     => ['Giỏ hàng', '/index.php?module=cart'],
    'orders'   => ['Đơn hàng của tôi', '/index.php?module=don-hang'],
];
$activeSection = in_array($activeNav ?? '', ['cart', 'checkout', 'success'], true) ? 'cart' : ($activeNav ?? '');
?>
<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-3">

            <!-- Brand -->
            <a href="/index.php?module=lookbook&tab=lookbook" class="flex items-center space-x-3 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-bold text-lg shadow-sm">A</div>
                <div class="hidden sm:block">
                    <div class="font-extrabold text-slate-900 tracking-tight leading-tight">Aura Studio</div>
                    <div class="text-[11px] font-semibold text-indigo-600 tracking-wider uppercase">Fabric Store &amp; Checkout</div>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-1 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
                <?php foreach ($navItems as $key => [$label, $href]): ?>
                    <?php $isActive = $activeSection === $key; ?>
                    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                       class="flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold transition-all <?= $isActive ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Right side -->
            <div class="flex items-center space-x-3">
                <a href="/index.php?module=cart" id="headerCartBtn"
                   class="relative flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm"
                   title="Mở giỏ hàng">
                    <i class="fa-solid fa-cart-shopping text-indigo-400"></i>
                    <span class="hidden sm:inline">Giỏ hàng</span>
                    <span id="headerCartCount"
                          class="min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-extrabold flex items-center justify-center">0</span>
                </a>

                <div class="hidden sm:flex items-center space-x-2 pl-3 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold">
                        <?= htmlspecialchars($firstLetter, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="hidden md:flex flex-col text-left">
                        <span class="text-xs font-bold text-slate-900 leading-tight" id="headerDisplayName"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[10px] font-semibold text-indigo-600"><?= htmlspecialchars($roleTitle, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
