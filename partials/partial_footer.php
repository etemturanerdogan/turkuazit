<?php
// ═══════════════════════════════════════════════════════════════════════════════
// DOSYA: partial_footer.php
// AÇIKLAMA: Site Alt Kısım (Footer) Şablonu
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya tüm sayfalarda görüntülenen alt bilgi alanını içerir.
// index.php tarafından her sayfa yüklemesinde dahil edilir.
// 
// İÇERİK:
// - Telif hakkı bilgisi (yıl otomatik güncellenir)
// - Doküman referansları (politika ve sözleşmeler)
// - HTML body ve html kapanış etiketleri
// ═══════════════════════════════════════════════════════════════════════════════
?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     SİTE FOOTER (Alt Bilgi Alanı)
     Telif hakkı ve yasal doküman linkleri.
     ═══════════════════════════════════════════════════════════════════════════ -->
<footer class="site-footer">
    <div class="container site-footer__inner">
        <!-- Telif Hakkı (Yıl otomatik güncellenir) -->
        <span>© <?php echo date('Y'); ?> TurkuazIT Bilişim Hizmetleri. Tüm hakları saklıdır.</span>
        
        <!-- Yasal Doküman Linkleri -->
        <span>
            Doküman seti:
            <a href="#" title="Aydınlatma Metni">T-AHS-R1.0</a> ·
            <a href="#" title="Gizlilik Politikası">T-GHP-R1.0</a> ·
            <a href="#" title="KVKK Bilgilendirme">T-KVKK-TFP-SR1.0</a>
        </span>
    </div>
</footer>

</body>
</html>
