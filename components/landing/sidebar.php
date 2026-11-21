<?php
/**
 * sidebar.php — sidebar lama untuk halaman user berbasis folder views/.
 *
 * Catatan penting:
 * - Layout publik Nexatech saat ini memakai menu di components/landing/header.php,
 *   jadi halaman baru (mainpage, katalog, teknisi) tidak memanggil file ini.
 * - File dipertahankan agar halaman lama yang masih memakai sidebar tidak rusak.
 * - Menu ditulis sebagai array agar penanda halaman aktif dan render <li>
 *   cukup dilakukan sekali di bawah.
 */
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$menuItems = [
  ['href' => 'dashboard.php', 'label' => 'Dashboard'],
  ['href' => 'products.php', 'label' => 'Produk'],
  ['href' => 'technician_booking.php', 'label' => 'Booking Teknisi'],
  ['href' => 'payment.php', 'label' => 'Pembayaran'],
  ['href' => 'logout.php', 'label' => 'Logout']
];
?>
<aside id="sidebar" class="fixed top-16 left-0 z-20 w-64 h-[calc(100vh-4rem)] transition-transform -translate-x-full sm:translate-x-0 bg-white border-r border-gray-200" aria-label="Sidebar">
  <div class="h-full px-3 py-4 overflow-y-auto">
    <ul class="space-y-2 font-medium">
      <?php foreach ($menuItems as $item) { ?>
        <?php // Halaman aktif dibandingkan lewat nama file agar sidebar tidak butuh JavaScript. ?>
        <?php $active = $item['href'] === $currentPage; ?>
        <li>
          <a href="<?php echo htmlspecialchars($item['href']); ?>" class="flex items-center p-2 rounded-lg <?php echo $active ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100'; ?>">
            <span class="ms-3"><?php echo htmlspecialchars($item['label']); ?></span>
          </a>
        </li>
      <?php } ?>
    </ul>
  </div>
</aside>
