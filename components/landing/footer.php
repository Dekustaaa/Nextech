<?php
/**
 * footer.php — footer publik bersama.
 * Dipisah dari halaman agar copyright dan tautan penting tidak perlu disalin
 * ulang, serta otomatis ikut berubah ketika layout.php diperbarui.
 */
?>
<footer class="site-footer">
    <div class="footer-inner">
        <!-- Identitas merek dan tahun berjalan. -->
        <div class="footer-brand">
            <a href="/PWL-SAS/mainpage.php" class="footer-logo">Nexatech<span>.</span></a>
            <span class="footer-copy">&copy; <?php echo date('Y'); ?> Nexatech. Teknologi untuk ide besarmu.</span>
        </div>
        <!-- Tautan cepat ke alur utama toko. -->
        <div class="footer-links">
            <a href="/PWL-SAS/pages/categories/index.php">Kategori &amp; brand</a>
            <a href="/PWL-SAS/pages/technician/">Booking teknisi</a>
            <a href="/PWL-SAS/views/recommendations.php">Nexsearch</a>
            <a href="/PWL-SAS/views/cart.php">Keranjang</a>
        </div>
    </div>
</footer>
