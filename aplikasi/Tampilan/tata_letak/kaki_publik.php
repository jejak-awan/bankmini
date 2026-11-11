<?php
/**
 * Tata Letak Kaki Publik (Public Shell Footer) Bank Mini.
 * Hanya memuat skrip interaksi dasar (`inti-ui.js`) dan Bootstrap JS tanpa modal atau skrip dasbor admin.
 */
$versiAsetKaki = (string) ($versiAplikasi ?? '1.0.0');
?>
<script src="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/js/bootstrap.bundle.min.js?v=' . $versiAsetKaki)) ?>"></script>
<script src="<?= amankanHtml(urlDasar('aset/js/inti-ui.js?v=' . $versiAsetKaki)) ?>"></script>
</body>
</html>

