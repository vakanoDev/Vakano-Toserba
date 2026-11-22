<?php
$page_title = 'Point of Sale (Kasir)';
require_once __DIR__ . '/includes/header.php';

requireKasirOrSuperAdmin();

$margin_aktif = getActiveMargin($pdo);
?>

<div class="h-[calc(100vh-6.5rem)] flex flex-col lg:flex-row gap-4">
    <!-- LEFT: Product Catalog & Search -->
    <div class="flex-1 flex flex-col bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Top Filter & Search Bar -->
        <div class="p-4 border-b border-slate-100 space-y-3 bg-slate-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="searchItem" placeholder="Cari nama barang..."
                        class="w-full pl-10 pr-4 py-2 bg-white rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
                <!-- Filter Tabs -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                    <button type="button" onclick="filterCategory('ALL')" id="tab-ALL"
                        class="cat-tab px-3 py-1.5 rounded-lg bg-emerald-600 text-white shadow-xs transition">
                        Semua
                    </button>
                    <button type="button" onclick="filterCategory('M')" id="tab-M"
                        class="cat-tab px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        Makanan (M)
                    </button>
                    <button type="button" onclick="filterCategory('N')" id="tab-N"
                        class="cat-tab px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        Non-Makanan (N)
                    </button>
                </div>
            </div>
        </div>

        <!-- Product Grid List -->
        <div class="flex-1 overflow-y-auto p-4">
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                <!-- Produk dimuat via JavaScript -->
                <div class="col-span-full py-16 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-600 mb-2"></i>
                    <p class="text-sm">Memuat katalog barang...</p>
                </div>
            </div>
        </div>

        <!-- Footer Catalog Info -->
        <div class="p-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Margin Aktif: <strong><?= $margin_aktif['persen'] ?>%</strong></span>
            </div>
            <div id="itemCountInfo">0 Barang ditemukan</div>
        </div>
    </div>

    <!-- RIGHT: Cart & Checkout Panel -->
    <div class="w-full lg:w-[380px] xl:w-[420px] flex flex-col bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Cart Header -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-emerald-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xs shadow-sm">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm">Keranjang Kasir</h3>
                    <p class="text-[11px] text-slate-500" id="cartItemCount">0 item</p>
                </div>
            </div>
            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-700 font-semibold hover:underline">
                Reset
            </button>
        </div>

        <!-- Cart Items List -->
        <div id="cartItemsList" class="flex-1 overflow-y-auto p-4 divide-y divide-slate-100 space-y-3">
            <div class="py-16 text-center text-slate-400">
                <i class="fa-solid fa-basket-shopping text-3xl text-slate-300 mb-2 block"></i>
                <p class="text-xs font-semibold">Keranjang Masih Kosong</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pilih barang dari katalog untuk memulai</p>
            </div>
        </div>

        <!-- Cart Calculation & Payment -->
        <div class="p-4 bg-slate-50/80 border-t border-slate-200 space-y-3 text-xs">
            <!-- Subtotal & PPN -->
            <div class="space-y-1.5 text-slate-600">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-800" id="textSubtotal">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>PPN (11%)</span>
                    <span class="font-bold text-slate-800" id="textPpn">Rp 0</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-extrabold text-emerald-800">
                    <span>Total Tagihan</span>
                    <span class="text-base text-emerald-700" id="textTotal">Rp 0</span>
                </div>
            </div>

            <!-- Input Pembayaran -->
            <div class="space-y-1.5 pt-1">
                <label class="block font-bold text-slate-700 text-[11px] uppercase tracking-wider">Uang Pembayaran (Rp)</label>
                <input type="number" id="inputBayar" placeholder="Masukkan jumlah uang tunai"
                    class="w-full px-3 py-2.5 bg-white rounded-xl border border-slate-300 font-mono text-base font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                
                <!-- Quick Amount Buttons -->
                <div class="grid grid-cols-4 gap-1.5 pt-1">
                    <button type="button" onclick="setExactAmount()" class="py-1 px-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-100">Uang Pas</button>
                    <button type="button" onclick="addAmount(20000)" class="py-1 px-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-100">+20rb</button>
                    <button type="button" onclick="addAmount(50000)" class="py-1 px-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-100">+50rb</button>
                    <button type="button" onclick="addAmount(100000)" class="py-1 px-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-100">+100rb</button>
                </div>
            </div>

            <!-- Kembalian -->
            <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex justify-between items-center">
                <span class="font-bold text-slate-600 text-[11px]">Kembalian:</span>
                <span class="text-sm font-extrabold text-slate-800" id="textKembalian">Rp 0</span>
            </div>

            <!-- Tombol Checkout -->
            <button type="button" id="btnCheckout" onclick="processCheckout()"
                class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-600/20 text-sm transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>Bayar & Cetak Nota</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL STRUK NOTA PEMBELIAN -->
<div id="modalReceipt" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeReceiptModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <!-- Printable Receipt Body -->
        <div id="printableArea" class="font-mono text-xs text-slate-700 leading-relaxed">
            <div class="text-center pb-3 border-b border-dashed border-slate-300">
                <div class="font-extrabold text-base tracking-widest text-emerald-800 uppercase">VAKANO TOSERBA</div>
                <div class="text-[10px] text-slate-500">Jl. Raya Kampus No. 1, Kota</div>
                <div class="text-[10px] text-slate-500">Telp: 0812-3456-7890</div>
            </div>

            <div class="py-2 border-b border-dashed border-slate-300 text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>No. Nota</span>
                    <span id="receiptId">#0</span>
                </div>
                <div class="flex justify-between">
                    <span>Tanggal</span>
                    <span id="receiptDate">-</span>
                </div>
                <div class="flex justify-between">
                    <span>Kasir</span>
                    <span id="receiptCashier">-</span>
                </div>
            </div>

            <!-- Items -->
            <div class="py-2 border-b border-dashed border-slate-300 space-y-1.5" id="receiptItems">
                <!-- Items list -->
            </div>

            <!-- Totaling -->
            <div class="py-2 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="receiptSubtotal">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>PPN (11%)</span>
                    <span id="receiptPpn">Rp 0</span>
                </div>
                <div class="flex justify-between font-bold text-slate-900 text-xs pt-1">
                    <span>TOTAL</span>
                    <span id="receiptTotal">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>Tunai</span>
                    <span id="receiptCash">Rp 0</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>Kembali</span>
                    <span id="receiptChange">Rp 0</span>
                </div>
            </div>

            <div class="text-center pt-3 text-[10px] text-slate-500">
                <p>Terima kasih telah berbelanja di Vakano Toserba!</p>
                <p>Barang yang sudah dibeli tidak dapat ditukar.</p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="mt-5 flex gap-2">
            <button onclick="printReceipt()" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </button>
            <button onclick="closeReceiptModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
let allProducts = [];
let cart = [];
let activeCategory = 'ALL';

// Format Rupiah Helper
function formatRupiah(number) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
}

// Load Katalog Produk
async function loadProducts() {
    try {
        const res = await fetch('ajax_pos.php?action=get_items');
        const json = await res.json();
        if (json.status === 'success') {
            allProducts = json.data;
            renderProducts();
        } else {
            Swal.fire('Error', json.message, 'error');
        }
    } catch (err) {
        console.error(err);
    }
}

// Filter Kategori
function filterCategory(cat) {
    activeCategory = cat;
    document.querySelectorAll('.cat-tab').forEach(b => {
        b.classList.remove('bg-emerald-600', 'text-white', 'shadow-xs');
        b.classList.add('text-slate-600');
    });
    const activeBtn = document.getElementById('tab-' + cat);
    if (activeBtn) {
        activeBtn.classList.add('bg-emerald-600', 'text-white', 'shadow-xs');
        activeBtn.classList.remove('text-slate-600');
    }
    renderProducts();
}

// Render Produk ke Grid
function renderProducts() {
    const searchVal = document.getElementById('searchItem').value.toLowerCase().trim();
    const grid = document.getElementById('productGrid');

    const filtered = allProducts.filter(item => {
        const matchName = item.nama.toLowerCase().includes(searchVal);
        const matchCat = (activeCategory === 'ALL') || (item.jenis === activeCategory);
        return matchName && matchCat;
    });

    document.getElementById('itemCountInfo').innerText = `${filtered.length} Barang ditemukan`;

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full py-16 text-center text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300 mb-2 block"></i>
                <p class="text-sm">Tidak ada barang yang cocok</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = filtered.map(item => {
        const stok = parseInt(item.stok || 0);
        const isOutOfStock = stok <= 0;
        const hargaJual = parseInt(item.harga_jual || 0);

        return `
            <div class="bg-white rounded-2xl border border-slate-200 p-3.5 flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition relative group ${isOutOfStock ? 'opacity-60' : ''}">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md ${item.jenis === 'M' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'}">
                            ${item.jenis === 'M' ? 'Makanan' : 'Non-Food'}
                        </span>
                        <span class="text-[10px] font-semibold ${isOutOfStock ? 'text-rose-600 font-bold' : 'text-slate-500'}">
                            Stok: ${stok} ${item.nama_satuan}
                        </span>
                    </div>
                    <h4 class="font-bold text-slate-800 text-xs sm:text-sm line-clamp-2 leading-tight">${item.nama}</h4>
                </div>

                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-400">Harga Jual</div>
                        <div class="text-xs sm:text-sm font-extrabold text-emerald-700">${formatRupiah(hargaJual)}</div>
                    </div>
                    <button type="button" onclick="addToCart(${item.idbarang})"
                        ${isOutOfStock ? 'disabled' : ''}
                        class="w-8 h-8 rounded-xl ${isOutOfStock ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs shadow-emerald-600/30'} flex items-center justify-center transition">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Tambah Produk ke Keranjang
function addToCart(idbarang) {
    const item = allProducts.find(p => p.idbarang == idbarang);
    if (!item) return;

    const stok = parseInt(item.stok || 0);
    if (stok <= 0) {
        Swal.fire('Stok Kosong', 'Barang ini saat ini tidak memiliki stok!', 'warning');
        return;
    }

    const exist = cart.find(c => c.idbarang == idbarang);
    if (exist) {
        if (exist.qty + 1 > stok) {
            Swal.fire('Batas Stok', `Maksimal pembelian untuk ${item.nama} adalah stok yang tersedia (${stok})`, 'warning');
            return;
        }
        exist.qty += 1;
    } else {
        cart.push({
            idbarang: item.idbarang,
            nama: item.nama,
            satuan: item.nama_satuan,
            harga: parseInt(item.harga_jual),
            stok: stok,
            qty: 1
        });
    }

    renderCart();
}

// Update Qty Keranjang
function updateCartQty(idbarang, delta) {
    const exist = cart.find(c => c.idbarang == idbarang);
    if (!exist) return;

    const newQty = exist.qty + delta;
    if (newQty <= 0) {
        removeFromCart(idbarang);
        return;
    }

    if (newQty > exist.stok) {
        Swal.fire('Batas Stok', `Jumlah melebihi stok tersedia (${exist.stok})`, 'warning');
        return;
    }

    exist.qty = newQty;
    renderCart();
}

// Hapus dari Keranjang
function removeFromCart(idbarang) {
    cart = cart.filter(c => c.idbarang != idbarang);
    renderCart();
}

// Kosongkan Keranjang
function clearCart() {
    if (cart.length === 0) return;
    cart = [];
    renderCart();
    document.getElementById('inputBayar').value = '';
}

// Hitung Kalkulasi Keranjang
function calculateCart() {
    let subtotal = 0;
    cart.forEach(item => {
        subtotal += item.harga * item.qty;
    });

    const ppn = Math.round(subtotal * 11 / 100);
    const total = subtotal + ppn;

    return { subtotal, ppn, total };
}

// Render Cart HTML
function renderCart() {
    const container = document.getElementById('cartItemsList');
    document.getElementById('cartItemCount').innerText = `${cart.length} barang terpilih`;

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="py-16 text-center text-slate-400">
                <i class="fa-solid fa-basket-shopping text-3xl text-slate-300 mb-2 block"></i>
                <p class="text-xs font-semibold">Keranjang Masih Kosong</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pilih barang dari katalog untuk memulai</p>
            </div>
        `;
        document.getElementById('textSubtotal').innerText = 'Rp 0';
        document.getElementById('textPpn').innerText = 'Rp 0';
        document.getElementById('textTotal').innerText = 'Rp 0';
        calculateChange();
        return;
    }

    container.innerHTML = cart.map(item => {
        const itemTotal = item.harga * item.qty;
        return `
            <div class="pt-3 first:pt-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h5 class="font-bold text-slate-800 text-xs">${item.nama}</h5>
                        <div class="text-[11px] text-slate-400">${formatRupiah(item.harga)} / ${item.satuan}</div>
                    </div>
                    <button onclick="removeFromCart(${item.idbarang})" class="text-slate-400 hover:text-rose-600 transition">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg p-1">
                        <button onclick="updateCartQty(${item.idbarang}, -1)" class="w-6 h-6 rounded bg-white text-slate-700 hover:bg-slate-200 text-xs font-bold flex items-center justify-center">-</button>
                        <span class="w-8 text-center font-bold text-xs">${item.qty}</span>
                        <button onclick="updateCartQty(${item.idbarang}, 1)" class="w-6 h-6 rounded bg-white text-slate-700 hover:bg-slate-200 text-xs font-bold flex items-center justify-center">+</button>
                    </div>
                    <div class="font-extrabold text-xs text-slate-800">${formatRupiah(itemTotal)}</div>
                </div>
            </div>
        `;
    }).join('');

    const calc = calculateCart();
    document.getElementById('textSubtotal').innerText = formatRupiah(calc.subtotal);
    document.getElementById('textPpn').innerText = formatRupiah(calc.ppn);
    document.getElementById('textTotal').innerText = formatRupiah(calc.total);
    calculateChange();
}

// Uang Pas & Quick Add
function setExactAmount() {
    const calc = calculateCart();
    document.getElementById('inputBayar').value = calc.total;
    calculateChange();
}

function addAmount(val) {
    const current = parseInt(document.getElementById('inputBayar').value || 0);
    document.getElementById('inputBayar').value = current + val;
    calculateChange();
}

// Hitung Kembalian
function calculateChange() {
    const calc = calculateCart();
    const bayar = parseInt(document.getElementById('inputBayar').value || 0);
    const kembalian = bayar - calc.total;

    const elKembalian = document.getElementById('textKembalian');
    if (kembalian >= 0) {
        elKembalian.innerText = formatRupiah(kembalian);
        elKembalian.className = 'text-sm font-extrabold text-emerald-700';
    } else {
        elKembalian.innerText = 'Kurang ' + formatRupiah(Math.abs(kembalian));
        elKembalian.className = 'text-sm font-extrabold text-rose-600';
    }
}

document.getElementById('inputBayar').addEventListener('input', calculateChange);
document.getElementById('searchItem').addEventListener('input', renderProducts);

// Proses Checkout Transaksi
async function processCheckout() {
    if (cart.length === 0) {
        Swal.fire('Keranjang Kosong', 'Silakan pilih barang yang ingin dibeli terlebih dahulu!', 'warning');
        return;
    }

    const calc = calculateCart();
    const bayar = parseInt(document.getElementById('inputBayar').value || 0);

    if (bayar < calc.total) {
        Swal.fire('Uang Kurang', `Pembayaran masih kurang ${formatRupiah(calc.total - bayar)}!`, 'error');
        return;
    }

    const btn = document.getElementById('btnCheckout');
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...`;

    try {
        const payload = {
            items: cart,
            bayar_tunai: bayar
        };

        const res = await fetch('ajax_pos.php?action=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (json.status === 'success') {
            // Tampilkan Modal Struk
            showReceipt(json.data);
            // Reset keranjang
            cart = [];
            renderCart();
            document.getElementById('inputBayar').value = '';
            // Refresh data katalog agar stok terbaru langsung update
            loadProducts();
        } else {
            Swal.fire('Transaksi Gagal', json.message, 'error');
        }
    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Gagal memproses transaksi: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>Bayar & Cetak Nota</span>`;
    }
}

// Tampilkan Struk
function showReceipt(data) {
    document.getElementById('receiptId').innerText = '#' + data.idpenjualan;
    document.getElementById('receiptDate').innerText = data.created_at;
    document.getElementById('receiptCashier').innerText = data.kasir;

    const itemsContainer = document.getElementById('receiptItems');
    itemsContainer.innerHTML = data.items.map(item => `
        <div>
            <div class="font-bold">${item.nama}</div>
            <div class="flex justify-between text-[10px]">
                <span>${item.jumlah} x ${formatRupiah(item.harga_satuan)}</span>
                <span>${formatRupiah(item.subtotal)}</span>
            </div>
        </div>
    `).join('');

    document.getElementById('receiptSubtotal').innerText = formatRupiah(data.subtotal);
    document.getElementById('receiptPpn').innerText = formatRupiah(data.ppn);
    document.getElementById('receiptTotal').innerText = formatRupiah(data.total);
    document.getElementById('receiptCash').innerText = formatRupiah(data.bayar);
    document.getElementById('receiptChange').innerText = formatRupiah(data.kembali);

    document.getElementById('modalReceipt').classList.remove('hidden');
}

function closeReceiptModal() {
    document.getElementById('modalReceipt').classList.add('hidden');
}

function printReceipt() {
    window.print();
}

// Inisialisasi awal
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #modalReceipt, #modalReceipt * {
        visibility: visible;
    }
    #modalReceipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
        padding: 0;
    }
    #printableArea {
        width: 100%;
        max-width: 80mm;
        margin: 0 auto;
    }
    button {
        display: none !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
