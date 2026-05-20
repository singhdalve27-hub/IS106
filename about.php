<?php 
require_once 'includes/header.php'; 

// Kunin ang kasalukuyang impormasyon ng carenderia mula sa settings table
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (\PDOException $e) {
    $settings = null;
}

// Fallbacks kung sakaling walang laman ang database
$store_name = $settings ? htmlspecialchars($settings['store_name']) : 'Aking Carenderia';
$operating_hours = $settings ? htmlspecialchars($settings['operating_hours']) : '6:00 AM - 8:00 PM';
$contact_number = $settings ? htmlspecialchars($settings['contact_number']) : 'N/A';
$address = $settings ? htmlspecialchars($settings['address']) : 'Talibon, Bohol';
?>

<!-- About Us Title Section -->
<div class="text-center my-4">
    <h1 class="fw-bold text-dark">Tungkol sa Amin</h1>
    <p class="text-muted">Alamin ang aming kuwento, oras ng pagbubukas, at kung paano kami matatagpuan.</p>
</div>

<div class="row g-4 justify-content-center align-items-stretch">
    <!-- Left Column: Ang Kuwento ng Carenderia -->
    <div class="col-md-7">
        <div class="card h-100 border-0 shadow-sm rounded-3 p-4 bg-white">
            <h3 class="fw-bold text-danger mb-3">Maligayang Pagdating sa <?php echo $store_name; ?>!</h3>
            <p class="text-secondary style-prose">
                Ang aming carenderia ay nagsimula sa simpleng pangarap: ang maghain ng masasarap, malinis, at abot-kayang lutong bahay para sa bawat pamilya at manggagawa. Araw-araw, maaga kaming gumigising upang pumili ng mga pinakasariwang sangkap sa lokal na pamilihan.
            </p>
            <p class="text-secondary style-prose">
                Mula sa mainit na Sinigang, malinamnam na Adobo, hanggang sa mga pampalamig at panghimagas, tinitiyak namin na ang bawat subo ay may lasang pagmamahal at tradisyon ng tunay na pagkaing Pilipino.
            </p>
            <hr class="my-4 text-muted opacity-25">
            <h5 class="fw-bold text-dark mb-2">Bakit Kami ang Piliin?</h5>
            <ul class="text-secondary ps-3">
                <li class="mb-1">Laging bagong luto at mainit isineserbi araw-araw.</li>
                <li class="mb-1">Malinis at ligtas ang paghahanda sa aming kusina.</li>
                <li class="mb-1">Presyong swak sa bulsa at pang-pamilya.</li>
            </ul>
        </div>
    </div>

    <!-- Right Column: Store Details & Contact Card -->
    <div class="col-md-5">
        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden bg-white">
            <div class="bg-danger text-white p-4 text-center">
                <h4 class="fw-bold mb-0">ℹ️ Impormasyon</h4>
                <small class="opacity-75">Mga detalye ng aming tindahan</small>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="list-group list-group-flush w-100">
                    
                    <!-- Operating Hours Item -->
                    <div class="list-group-item px-0 py-3 bg-transparent border-bottom">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="fw-bold text-dark mb-1">⏰ Oras ng Pagbubukas</h6>
                        </div>
                        <p class="mb-0 text-muted small"><?php echo $operating_hours; ?></p>
                    </div>

                    <!-- Contact Number Item -->
                    <div class="list-group-item px-0 py-3 bg-transparent border-bottom">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="fw-bold text-dark mb-1">📞 Numero ng Telepono / GCash</h6>
                        </div>
                        <p class="mb-0 text-muted small"><?php echo $contact_number; ?></p>
                    </div>

                    <!-- Address/Location Item -->
                    <div class="list-group-item px-0 py-3 bg-transparent border-0">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="fw-bold text-dark mb-1">📍 Lokasyon</h6>
                        </div>
                        <p class="mb-0 text-muted small"><?php echo $address; ?></p>
                    </div>

                </div>

                <!-- Footer Call-to-action button inside card -->
                <div class="pt-4 text-center mt-auto">
                    <a href="menu.php" class="btn btn-outline-danger w-100 fw-bold rounded-pill py-2">
                        🍳 Silipin ang mga Ulam Ngayon
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>