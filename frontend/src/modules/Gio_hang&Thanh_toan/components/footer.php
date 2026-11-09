    <footer class="mt-12 border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
            <p>© <?= date('Y') ?> Aura Fabrics · Hệ thống cung ứng vải may mặc cao cấp.</p>
            <div class="flex items-center gap-4">
                <span><i class="fa-solid fa-phone mr-1 text-slate-400"></i>0988.242.868</span>
                <span><i class="fa-solid fa-qrcode mr-1 text-slate-400"></i>VietQR</span>
                <span><i class="fa-solid fa-truck-fast mr-1 text-slate-400"></i>COD</span>
            </div>
        </div>
    </footer>

    <script>
        (function syncHeaderUser() {
            try {
                const cart = JSON.parse(localStorage.getItem('aura_lookbook_cart') || '[]');
                const badge = document.getElementById('headerCartCount');
                if (badge) badge.textContent = Array.isArray(cart) ? cart.length : 0;

                const user = JSON.parse(localStorage.getItem('aura_user') || 'null');
                if (user) {
                    const nameEl = document.getElementById('headerDisplayName');
                    const name = user.TenKH || user.TenNV || user.TenTK || 'Khách';
                    if (nameEl) nameEl.textContent = name;
                }
            } catch (e) {}
        })();
    </script>
</body>
</html>
