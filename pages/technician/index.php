<?php
/**
 * index.php (teknisi) — halaman booking teknisi Nexatech.
 *
 * Alur data:
 * 1. Ambil daftar teknisi dari tabel technicians dan layanan dari repository.
 * 2. Siapkan data awal untuk JavaScript (teknisi/layanan terpilih).
 * 3. Panggil layout.php agar header/footer sama dengan mainpage.
 * 4. Form dikirim ke actions/technician_booking.php untuk diproses PHP.
 *
 * Catatan: file ini hanya menampilkan dan memvalidasi di sisi browser.
 * Validasi akhir serta penyimpanan booking tetap dilakukan di actions/.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/services.php";

/* ------------------------------------------------------------------
 |  FOTO & PROFIL TAMBAHAN TEKNISI
 |  Foto utama diambil dari kolom photo_url di tabel technicians. Daftar
 |  cadangan hanya dipakai jika URL foto di database masih kosong.
 |  Pengalaman dan jumlah ulasan tetap dipetakan di sini karena belum
 |  tersedia kolomnya di database.
 * ------------------------------------------------------------------ */
$foto_cadangan = [
    "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&h=400&fit=crop&crop=faces&q=80",
    "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&fit=crop&crop=faces&q=80",
    "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&h=400&fit=crop&crop=faces&q=80",
];
$pengalaman_teknisi = [1 => 6, 2 => 4, 3 => 8, 4 => 5, 5 => 3];
$ulasan_teknisi     = [1 => 84, 2 => 62, 3 => 120, 4 => 73, 5 => 41];

/* ------------------------------------------------------------------
 |  AMBIL DATA TEKNISI
 * ------------------------------------------------------------------ */
$semua_teknisi = [];
$query = mysqli_query($conn, "SELECT * FROM technicians ORDER BY (status = 'tersedia') DESC, rating DESC, technician_id ASC");
while ($row = mysqli_fetch_assoc($query)) {
    $id = (int)$row['technician_id'];
    $semua_teknisi[] = [
        'id'         => $id,
        'nama'       => $row['technician_name'],
        'spesialis'  => $row['specialty'] ?: 'Teknisi umum',
        'telepon'    => $row['phone'] ?? '',
        'rating'     => (float)$row['rating'],
        'status'     => $row['status'] ?: 'tersedia',
        'online'     => ($row['status'] ?? '') === 'tersedia',
        'foto'       => !empty($row['photo_url']) ? $row['photo_url'] : $foto_cadangan[($id - 1) % count($foto_cadangan)],
        'pengalaman' => $pengalaman_teknisi[$id] ?? (3 + ($id % 5)),
        'ulasan'     => $ulasan_teknisi[$id] ?? (20 + $id * 7),
    ];
}

$available_techs = array_values(array_filter($semua_teknisi, function ($t) {
    return $t['online'];
}));
$teknisi_tampil = $available_techs;

/* ------------------------------------------------------------------
 |  LAYANAN
 * ------------------------------------------------------------------ */
$layanan = get_services($conn);

/* ------------------------------------------------------------------
 |  FLASH MESSAGE
 * ------------------------------------------------------------------ */
$old    = $_SESSION['booking_old'] ?? [];
$error  = $_SESSION['booking_error'] ?? '';
$sukses = $_SESSION['booking_sukses'] ?? null;
unset($_SESSION['booking_old'], $_SESSION['booking_error'], $_SESSION['booking_sukses']);

$nilai = function ($key, $default = '') use ($old) {
    return htmlspecialchars($old[$key] ?? $default);
};

/* ------------------------------------------------------------------
 |  TEKNISI & LAYANAN TERPILIH
 * ------------------------------------------------------------------ */
$teknisi_awal = null;
if (!empty($old['technician_id'])) {
    foreach ($teknisi_tampil as $t) {
        if ((int)$t['id'] === (int)$old['technician_id']) {
            $teknisi_awal = $t;
            break;
        }
    }
}
if (!$teknisi_awal && !empty($teknisi_tampil)) {
    $teknisi_awal = $teknisi_tampil[0];
}
$teknisi_awal_id = $teknisi_awal ? (int)$teknisi_awal['id'] : 0;

$layanan_awal = null;
if (!empty($old['service_id'])) {
    foreach ($layanan as $s) {
        if ((int)$s['service_id'] === (int)$old['service_id']) {
            $layanan_awal = $s;
            break;
        }
    }
}
if (!$layanan_awal && !empty($layanan)) {
    $layanan_awal = $layanan[0];
}
$layanan_awal_id = $layanan_awal ? (int)$layanan_awal['service_id'] : 0;

/* ------------------------------------------------------------------
 |  DATA UNTUK JAVASCRIPT
 * ------------------------------------------------------------------ */
$data_js = [
    'technicians'       => $semua_teknisi,
    'services'          => array_map(function ($s) {
        return [
            'id'          => (int)$s['service_id'],
            'nama'        => $s['service_name'],
            'harga'       => (float)$s['base_price'],
            'deskripsi'   => $s['description'] ?? '',
        ];
    }, $layanan),
    'selectedTechnician' => $teknisi_awal_id,
    'selectedService'    => $layanan_awal_id,
    'success'            => $sukses,
];
// Flag HEX_* mencegah data teks menutup tag <script> atau merusak atribut HTML.
$data_js_json = json_encode($data_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

/* ------------------------------------------------------------------
 |  LAYOUT BERSAMA
 |  Header/footer dan aset global tidak lagi disalin di halaman ini.
 |  layout.php memuat theme.css + header yang sama dengan mainpage.
 * ------------------------------------------------------------------ */
require_once __DIR__ . "/../../components/landing/layout.php";

// Menandai menu Booking sebagai menu aktif di header bersama.
$GLOBALS['activeNav'] = 'booking';

// Aset tambahan khusus booking. theme.css tetap dimuat lebih dulu oleh layout,
// lalu technician.css menambahkan komponen yang hanya dipakai halaman ini.
$extraHead = '<meta name="description" content="Pesan teknisi profesional untuk kebutuhan perangkat Anda. Pilih teknisi, layanan, jadwal, dan kirim permintaan dalam satu halaman.">'
    . '<link rel="stylesheet" href="/PWL-SAS/assets/css/technician.css?v=' . filemtime(__DIR__ . '/../../assets/css/technician.css') . '">';

page_start('Booking Teknisi - Nexatech', $extraHead);
?>


<!-- ================================================================
     KONTEN
 * ================================================================ -->
<!-- page-shell adalah pembungkus konten booking; <main> sudah disediakan layout.php. -->
<div class="page-shell">
    <div class="booking-layout">

        <!-- ================= KOLOM KIRI ================= -->
        <section class="booking-main">

            <!-- Hero -->
            <section class="hero-card" aria-labelledby="hero-title">
                <span class="hero-blob hero-blob-1" aria-hidden="true"></span>
                <span class="hero-blob hero-blob-2" aria-hidden="true"></span>

                <div class="hero-copy">
                    <p class="hero-eyebrow">Book a Technician</p>
                    <h1 id="hero-title" class="hero-title">
                        Find the right technician<br class="hidden sm:block">
                        for your device needs
                    </h1>
                    <p class="hero-description">
                        Fill in the details below to schedule a service request.<br class="hidden sm:block">
                        Our technician will get back to you as soon as possible.
                    </p>
                </div>

                <div class="hero-media" aria-hidden="true">
                    <img
                        src="https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=900&auto=format&fit=crop&q=80"
                        alt=""
                        loading="eager"
                    >
                    <span class="hero-media-fade"></span>
                    <span class="script-tag">Fast<br>Reliable<br>Professional</span>
                    <svg class="script-arrow" viewBox="0 0 76 42" fill="none" aria-hidden="true">
                        <path d="M3 5c19 3 34 11 43 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M39 28c3-4 6-7 10-9M46 29c-1 5-1 8 0 11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </div>
            </section>

            <!-- Panel utama -->
            <section class="panel main-panel" aria-labelledby="available-title">

                <div class="available-head">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <span class="available-icon">
                            <i data-lucide="user-round-check" class="w-[21px] h-[21px]"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 id="available-title" class="available-title">Available Technician</h2>
                            <p class="available-subtitle">Choose a technician based on your needs.</p>
                        </div>
                    </div>
                    <button type="button" id="view-all-tech" class="view-all-btn">
                        View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <?php if ($teknisi_awal) { ?>
                    <article class="featured-tech" id="featured-tech" data-tech-id="<?php echo $teknisi_awal_id; ?>">
                        <div class="featured-photo-wrap">
                            <img id="featured-tech-photo"
                                 src="<?php echo htmlspecialchars($teknisi_awal['foto']); ?>"
                                 alt="<?php echo htmlspecialchars($teknisi_awal['nama']); ?>"
                                 class="featured-photo">
                        </div>

                        <div class="featured-info">
                            <div class="featured-topline">
                                <span class="online-badge" id="featured-online" data-i18n-skip>
                                    <span class="online-dot"></span> Online
                                </span>
                                <span class="featured-caption">Teknisi pilihan</span>
                            </div>

                            <h3 class="featured-name" id="featured-tech-name"><?php echo htmlspecialchars($teknisi_awal['nama']); ?></h3>
                            <p class="featured-specialty" id="featured-tech-specialty"><?php echo htmlspecialchars($teknisi_awal['spesialis']); ?></p>

                            <div class="featured-badges">
                                <span class="pill-verified">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    Verified Technician
                                </span>
                                <span class="pill-experience">
                                    <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                                    <span><span id="featured-tech-exp"><?php echo (int)$teknisi_awal['pengalaman']; ?></span>+ Years Experience</span>
                                </span>
                            </div>

                            <div class="featured-rating">
                                <i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-400"></i>
                                <strong id="featured-tech-rating"><?php echo number_format((float)$teknisi_awal['rating'], 1, ',', '.'); ?></strong>
                                <span id="featured-tech-reviews" data-i18n-skip>(<?php echo (int)$teknisi_awal['ulasan']; ?> Reviews)</span>
                            </div>
                        </div>

                        <button type="button" id="next-tech" class="featured-arrow" title="Lihat teknisi berikutnya" aria-label="Lihat teknisi berikutnya">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </button>
                    </article>

                    <div id="tech-dots" class="tech-dots" aria-label="Pilih teknisi">
                        <?php foreach ($teknisi_tampil as $index => $t) { ?>
                            <button type="button"
                                    class="tech-dot <?php echo $index === 0 ? 'is-active' : ''; ?>"
                                    data-tech-id="<?php echo (int)$t['id']; ?>"
                                    aria-label="Pilih <?php echo htmlspecialchars($t['nama']); ?>"></button>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty-state">
                        <i data-lucide="user-round-x" class="w-5 h-5"></i>
                        <p>Belum ada teknisi yang terdaftar. Anda tetap bisa mengirim permintaan dan kami akan mencarikan teknisi untuk Anda.</p>
                    </div>
                <?php } ?>

                <?php if ($error) { ?>
                    <div class="form-alert" role="alert">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php } ?>

                <form action="/PWL-SAS/actions/technician_booking.php" method="POST" id="booking-form" class="booking-form" novalidate>
                    <div class="form-grid">
                        <!-- Nama -->
                        <div class="field">
                            <label class="field-label" for="fullname">
                                <i data-lucide="user-round" class="w-3.5 h-3.5"></i> Fullname
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="user-round" class="field-icon"></i>
                                <input type="text" id="fullname" name="nama" class="field-input"
                                       placeholder="Fill the fullname" autocomplete="name"
                                       value="<?php echo $nilai('nama'); ?>" required>
                            </div>
                        </div>

                        <!-- Telepon -->
                        <div class="field">
                            <label class="field-label" for="phone">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i> Phone Number
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="phone" class="field-icon"></i>
                                <input type="tel" id="phone" name="telepon" class="field-input"
                                       placeholder="+62 8789 6767 676" autocomplete="tel"
                                       value="<?php echo $nilai('telepon'); ?>" required>
                            </div>
                        </div>

                        <!-- Layanan -->
                        <div class="field">
                            <label class="field-label" for="service">
                                <i data-lucide="wrench" class="w-3.5 h-3.5"></i> Type of Services
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="wrench" class="field-icon"></i>
                                <select id="service" name="service_id" class="field-input field-select" required>
                                    <?php if (empty($layanan)) { ?>
                                        <option value="">Belum ada layanan</option>
                                    <?php } ?>
                                    <?php foreach ($layanan as $s) { ?>
                                        <option value="<?php echo (int)$s['service_id']; ?>"
                                            <?php echo $layanan_awal_id === (int)$s['service_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['service_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <i data-lucide="chevron-down" class="select-chevron"></i>
                            </div>
                        </div>

                        <!-- Teknisi -->
                        <div class="field">
                            <label class="field-label" for="technician">
                                <i data-lucide="user-round" class="w-3.5 h-3.5"></i> Technician
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="user-round" class="field-icon"></i>
                                <select id="technician" name="technician_id" class="field-input field-select" required>
                                    <?php if (empty($available_techs)) { ?>
                                        <option value="0">Pilih otomatis</option>
                                    <?php } ?>
                                    <?php foreach ($available_techs as $t) { ?>
                                        <option value="<?php echo (int)$t['id']; ?>"
                                            <?php echo $teknisi_awal_id === (int)$t['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($t['nama']); ?> - <?php echo htmlspecialchars($t['spesialis']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <i data-lucide="chevron-down" class="select-chevron"></i>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="field">
                            <label class="field-label" for="visit-date">
                                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i> Date of Visit
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="calendar-days" class="field-icon"></i>
                                <input type="date" id="visit-date" name="tanggal" class="field-input"
                                       min="<?php echo date('Y-m-d'); ?>"
                                       value="<?php echo $nilai('tanggal'); ?>" required>
                            </div>
                        </div>

                        <!-- Jam -->
                        <div class="field">
                            <label class="field-label" for="visit-time">
                                <i data-lucide="clock-3" class="w-3.5 h-3.5"></i> Time
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="clock-3" class="field-icon"></i>
                                <input type="time" id="visit-time" name="jam" class="field-input"
                                       value="<?php echo $nilai('jam'); ?>" required>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="field field-full">
                            <label class="field-label" for="address">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Complete Address
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="map-pin" class="field-icon field-icon-textarea"></i>
                                <textarea id="address" name="alamat" class="field-input field-textarea"
                                          placeholder="Fill your address" rows="2" required><?php echo $nilai('alamat'); ?></textarea>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="field field-full">
                            <label class="field-label" for="notes">
                                <i data-lucide="message-square-text" class="w-3.5 h-3.5"></i> Note/Complain
                            </label>
                            <div class="input-wrap">
                                <i data-lucide="message-square-text" class="field-icon field-icon-textarea"></i>
                                <textarea id="notes" name="keluhan" class="field-input field-textarea"
                                          placeholder="Fill your complain" rows="2"><?php echo $nilai('keluhan'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" id="submit-btn">
                        <span class="submit-label">
                            <i data-lucide="send" class="w-[17px] h-[17px]"></i>
                            <span>Send request</span>
                        </span>
                        <i data-lucide="arrow-right" class="w-[18px] h-[18px]"></i>
                    </button>
                </form>
            </section>
        </section>

        <!-- ================= KOLOM KANAN ================= -->
        <aside class="booking-side" aria-label="Ringkasan booking">

            <!-- Trusted -->
            <section class="trusted-banner">
                <span class="trusted-ring trusted-ring-1" aria-hidden="true"></span>
                <span class="trusted-ring trusted-ring-2" aria-hidden="true"></span>
                <div class="trusted-content">
                    <span class="trusted-icon">
                        <i data-lucide="shield-check" class="w-[22px] h-[22px]"></i>
                    </span>
                    <div>
                        <h2 class="trusted-title">Trusted &amp; Professional</h2>
                        <p class="trusted-copy">Our technicians are certified and ready to help you.</p>
                    </div>
                </div>
                <img
                    src="https://images.pexels.com/photos/8005368/pexels-photo-8005368.jpeg?auto=compress&cs=tinysrgb&w=700"
                    alt=""
                    class="trusted-photo"
                    loading="lazy"
                >
                <span class="trusted-photo-fade" aria-hidden="true"></span>
            </section>

            <!-- Ringkasan -->
            <section class="panel summary-card" aria-label="Ringkasan">
                <button type="button" class="summary-row" data-open-modal="summary">
                    <span class="summary-icon"><i data-lucide="clipboard-list" class="w-[19px] h-[19px]"></i></span>
                    <span class="summary-copy">
                        <span class="summary-title">Booking Summary</span>
                        <span class="summary-sub">View your booking details</span>
                    </span>
                    <i data-lucide="chevron-right" class="summary-chevron"></i>
                </button>

                <button type="button" class="summary-row" data-open-modal="technician">
                    <span class="summary-icon"><i data-lucide="user-round" class="w-[19px] h-[19px]"></i></span>
                    <span class="summary-copy">
                        <span class="summary-title">Technician</span>
                        <span class="summary-sub" id="summary-tech-name" data-i18n-skip><?php echo $teknisi_awal ? htmlspecialchars($teknisi_awal['nama']) : 'Pilih otomatis'; ?></span>
                    </span>
                    <?php if ($teknisi_awal) { ?>
                        <img id="summary-tech-photo" class="summary-avatar" src="<?php echo htmlspecialchars($teknisi_awal['foto']); ?>" alt="">
                    <?php } else { ?>
                        <img id="summary-tech-photo" class="summary-avatar" src="" alt="" hidden>
                    <?php } ?>
                    <i data-lucide="chevron-right" class="summary-chevron"></i>
                </button>

                <button type="button" class="summary-row" data-open-modal="services">
                    <span class="summary-icon"><i data-lucide="settings" class="w-[19px] h-[19px]"></i></span>
                    <span class="summary-copy">
                        <span class="summary-title">Services</span>
                        <span class="summary-sub" id="summary-service-name" data-i18n-skip><?php echo $layanan_awal ? htmlspecialchars($layanan_awal['service_name']) : 'Belum dipilih'; ?></span>
                    </span>
                    <i data-lucide="chevron-right" class="summary-chevron"></i>
                </button>
            </section>

            <!-- Teknisi terpilih -->
            <section class="panel selected-section" aria-labelledby="selected-title">
                <div class="selected-head">
                    <h2 id="selected-title" class="selected-title">Your Selected Technician</h2>
                    <span class="online-badge"><span class="online-dot"></span> Online</span>
                </div>

                <div class="selected-tech-panel">
                    <img id="selected-tech-photo"
                         src="<?php echo $teknisi_awal ? htmlspecialchars($teknisi_awal['foto']) : ''; ?>"
                         alt="<?php echo $teknisi_awal ? htmlspecialchars($teknisi_awal['nama']) : ''; ?>"
                         class="selected-photo">

                    <div class="selected-info">
                        <h3 id="selected-tech-name"><?php echo $teknisi_awal ? htmlspecialchars($teknisi_awal['nama']) : 'Belum dipilih'; ?></h3>
                        <p id="selected-tech-specialty"><?php echo $teknisi_awal ? htmlspecialchars($teknisi_awal['spesialis']) : 'Pilih teknisi terlebih dahulu'; ?></p>
                        <div class="selected-rating">
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-400"></i>
                            <strong id="selected-tech-rating"><?php echo $teknisi_awal ? number_format((float)$teknisi_awal['rating'], 1, ',', '.') : '-'; ?></strong>
                            <span id="selected-tech-reviews" data-i18n-skip><?php echo $teknisi_awal ? '(' . (int)$teknisi_awal['ulasan'] . ' Reviews)' : ''; ?></span>
                        </div>
                    </div>

                    <button type="button" id="change-tech" class="selected-arrow" aria-label="Ganti teknisi">
                        <i data-lucide="arrow-right" class="w-[18px] h-[18px]"></i>
                    </button>
                </div>

                <div class="selected-features">
                    <div class="selected-feature">
                        <span class="feature-icon"><i data-lucide="shield-check" class="w-4 h-4"></i></span>
                        <span>Verified</span>
                    </div>
                    <div class="selected-feature">
                        <span class="feature-icon"><i data-lucide="badge-check" class="w-4 h-4"></i></span>
                        <span><span id="selected-tech-years"><?php echo $teknisi_awal ? (int)$teknisi_awal['pengalaman'] : '0'; ?></span>+ Years</span>
                    </div>
                    <div class="selected-feature">
                        <span class="feature-icon"><i data-lucide="users-round" class="w-4 h-4"></i></span>
                        <span>Good Feedback</span>
                    </div>
                </div>
            </section>

            <!-- Kenapa memilih kami -->
            <section class="panel why-section" aria-labelledby="why-title">
                <h2 id="why-title" class="why-title">Why Choose Us?</h2>
                <div class="why-grid">
                    <div class="why-item">
                        <span class="why-icon"><i data-lucide="zap" class="w-[18px] h-[18px]"></i></span>
                        <div>
                            <p class="why-heading">Fast Response</p>
                            <p class="why-copy">Get help quickly</p>
                        </div>
                    </div>
                    <div class="why-item">
                        <span class="why-icon"><i data-lucide="users-round" class="w-[18px] h-[18px]"></i></span>
                        <div>
                            <p class="why-heading">Professional Team</p>
                            <p class="why-copy">Experienced &amp; certified</p>
                        </div>
                    </div>
                    <div class="why-item">
                        <span class="why-icon"><i data-lucide="credit-card" class="w-[18px] h-[18px]"></i></span>
                        <div>
                            <p class="why-heading">Secure Payment</p>
                            <p class="why-copy">Multiple payment methods</p>
                        </div>
                    </div>
                    <div class="why-item">
                        <span class="why-icon"><i data-lucide="shield-check" class="w-[18px] h-[18px]"></i></span>
                        <div>
                            <p class="why-heading">100% Satisfaction</p>
                            <p class="why-copy">Your trust is our priority</p>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

<!-- ================================================================
     MODAL: PILIH TEKNISI
 * ================================================================ -->
<div id="modal-technician" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-tech-title" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-panel modal-panel-lg">
        <div class="modal-head">
            <div>
                <p class="modal-eyebrow">Available Technician</p>
                <h2 id="modal-tech-title" class="modal-title">Pilih teknisi Anda</h2>
                <p class="modal-subtitle">Semua teknisi terverifikasi dan siap membantu.</p>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i data-lucide="x" class="w-[18px] h-[18px]"></i>
            </button>
        </div>

        <div class="modal-search">
            <i data-lucide="search" class="w-4 h-4"></i>
            <input type="search" id="tech-search" placeholder="Cari nama atau keahlian teknisi...">
        </div>

        <div id="tech-modal-list" class="modal-tech-list" data-i18n-skip></div>
        <p id="tech-empty" class="modal-empty" hidden>Tidak ada teknisi yang cocok dengan pencarian.</p>
    </div>
</div>

<!-- ================================================================
     MODAL: PILIH LAYANAN
 * ================================================================ -->
<div id="modal-services" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-service-title" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-panel modal-panel-md">
        <div class="modal-head">
            <div>
                <p class="modal-eyebrow">Our Services</p>
                <h2 id="modal-service-title" class="modal-title">Pilih jenis layanan</h2>
                <p class="modal-subtitle">Harga akhir menyesuaikan kondisi perangkat.</p>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i data-lucide="x" class="w-[18px] h-[18px]"></i>
            </button>
        </div>

        <div id="service-modal-list" class="modal-service-list" data-i18n-skip></div>
    </div>
</div>

<!-- ================================================================
     MODAL: RINGKASAN BOOKING
 * ================================================================ -->
<div id="modal-summary" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-summary-title" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-panel modal-panel-md">
        <div class="modal-head">
            <div>
                <p class="modal-eyebrow">Booking Summary</p>
                <h2 id="modal-summary-title" class="modal-title">Ringkasan permintaan</h2>
                <p class="modal-subtitle">Periksa kembali detail sebelum mengirim permintaan.</p>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i data-lucide="x" class="w-[18px] h-[18px]"></i>
            </button>
        </div>

        <div class="summary-detail">
            <div class="summary-detail-row">
                <span>Nama lengkap</span>
                <strong id="summary-name">-</strong>
            </div>
            <div class="summary-detail-row">
                <span>Nomor telepon</span>
                <strong id="summary-phone">-</strong>
            </div>
            <div class="summary-detail-row">
                <span>Layanan</span>
                <strong id="summary-service" data-i18n-skip>-</strong>
            </div>
            <div class="summary-detail-row">
                <span>Teknisi</span>
                <strong id="summary-technician" data-i18n-skip>-</strong>
            </div>
            <div class="summary-detail-row">
                <span>Jadwal</span>
                <strong id="summary-schedule">-</strong>
            </div>
            <div class="summary-detail-row">
                <span>Alamat</span>
                <strong id="summary-address">-</strong>
            </div>
            <div class="summary-detail-row">
                <span>Catatan</span>
                <strong id="summary-notes">-</strong>
            </div>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn-ghost" data-close-modal>Kembali</button>
            <button type="button" id="summary-send" class="btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i> Kirim permintaan
            </button>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: SUKSES
 * ================================================================ -->
<div id="modal-success" class="modal" role="dialog" aria-modal="true" aria-labelledby="success-title" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-panel modal-panel-md success-panel">
        <div class="success-check">
            <i data-lucide="check" class="w-8 h-8"></i>
        </div>
        <p class="modal-eyebrow">Request Sent</p>
        <h2 id="success-title" class="success-title">Permintaan berhasil dikirim!</h2>
        <p class="success-copy">Tim teknisi kami akan menghubungi Anda untuk konfirmasi jadwal.</p>

        <div class="success-code-box">
            <span>Nomor booking</span>
            <strong id="success-code"><?php echo htmlspecialchars($sukses['kode'] ?? '-'); ?></strong>
        </div>

        <div class="success-detail">
            <div><span>Teknisi</span><strong id="success-tech"><?php echo htmlspecialchars($sukses['teknisi'] ?? '-'); ?></strong></div>
            <div><span>Layanan</span><strong id="success-service"><?php echo htmlspecialchars($sukses['layanan'] ?? '-'); ?></strong></div>
            <div><span>Jadwal</span><strong id="success-schedule"><?php echo htmlspecialchars($sukses['jadwal'] ?? '-'); ?></strong></div>
        </div>

        <div class="modal-foot justify-center">
            <a href="/PWL-SAS/mainpage.php" class="btn-ghost">Kembali ke beranda</a>
            <button type="button" class="btn-primary" data-close-modal>Booking lagi</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" role="status" aria-live="polite">
    <span class="toast-icon"><i data-lucide="check" class="w-4 h-4"></i></span>
    <span id="toast-message" data-i18n-skip>Teknisi dipilih.</span>
</div>

<?php
/**
 * Script halaman booking dimuat lewat layout agar urutannya benar:
 * 1) data PHP dikirim ke JavaScript,
 * 2) technician.js membaca data tersebut,
 * 3) layout menutup halaman dan menginisialisasi ikon Lucide.
 */
$extraScripts = '<script>window.TECHHUB_BOOKING = ' . ($data_js_json ?: '{}') . ';</script>'
    . '<script src="/PWL-SAS/assets/js/technician.js?v=' . filemtime(__DIR__ . '/../../assets/js/technician.js') . '"></script>';

page_end($extraScripts);
?>
