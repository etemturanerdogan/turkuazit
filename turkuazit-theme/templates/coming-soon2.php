<?php
/*
 Template Name: Yakında Açılıyoruz - TurkuazIT
*/
?>

<div style="
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
">
    <div class="tq-card" style="max-width: 640px; width: 100%; text-align: center;">

        <!-- Logo -->
        <div style="margin-bottom: 20px;">
            <img 
                src="<?php echo get_template_directory_uri(); ?>/assets/img/turkuazit-logo.png"
                alt="TurkuazIT"
                style="height: 250px; max-width: 100%;"
            >
        </div>

        <!-- Başlık -->
        <h1 style="font-size: 32px; margin-bottom: 12px;">
            <span style="color: var(--tq-accent);">TurkuazIT</span> Yakında Hizmetinizde
        </h1>

        <!-- Alt açıklama -->
        <p style="font-size: 15px; margin-bottom: 24px;">
            Modüler BT hizmet mimarimizi, sözleşme altyapımızı ve teknik operasyon panelimizi
            sizin için hazırlıyoruz. Kısa süre içinde, tüm modüller ve dokümanlar
            <strong>bu alan adı üzerinden</strong> yayınlanacaktır.
        </p>

        <!-- İletişim butonu -->
        <div style="margin-bottom: 20px;">
            <a href="mailto:info@turkuazit.com" class="tq-btn">
                info@turkuazit.com ile İletişime Geç
            </a>
        </div>

        <!-- Kart içinde kart - kartvizit hissi -->
        <div class="tq-card" style="margin-top: 10px;">
            <p style="margin: 0 0 4px 0; font-size: 14px;">
                <strong>Etem Turan Erdoğan</strong>
            </p>
            <p style="margin: 0 0 2px 0; font-size: 13px;">
                Kurucu &amp; Teknik Operasyon – TurkuazIT
            </p>
            <p style="margin: 0; font-size: 13px;">
                Tel: <span style="color: var(--tq-accent);">0 (530) 707 43 82</span>
            </p>
        </div>

    </div>
</div>

<?php
get_footer();
?>
