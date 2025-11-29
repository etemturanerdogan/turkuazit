<?php
// coming-soon.php
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>TurkuazIT – Yakında Hizmetinizde</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Basit, modern font -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <style>
        :root {
            --bg-dark: #020617;
            --card-bg: rgba(15, 23, 42, 0.94);
            --border-soft: rgba(148, 163, 184, 0.45);
            --accent: #0ea5e9;
            --accent-2: #3b82f6;
            --text-main: #e5e7eb;
            --text-soft: #9ca3af;
            --text-mute: #6b7280;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #0ea5e9 0, #020617 52%, #000 100%);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .cs-wrapper {
            width: 100%;
            max-width: 720px;
        }

        .cs-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 28px 24px 24px;
            border: 1px solid var(--border-soft);
            box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.75),
                0 0 0 1px rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(18px);
        }

        .cs-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 10px;
            letter-spacing: .22em;
            text-transform: uppercase;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(56, 189, 248, 0.7);
            color: #e0f2fe;
        }

        .cs-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.22);
        }

        .cs-title {
            margin-top: 20px;
            font-size: 32px;
            line-height: 1.2;
            font-weight: 700;
            color: #f9fafb;
        }

        .cs-subtitle {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.7;
            color: #cbd5f5;
            max-width: 540px;
        }

        .cs-layout {
            margin-top: 22px;
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(0, 2.2fr);
            gap: 24px;
            align-items: flex-start;
        }

        /* Sol taraf – sayaç + mail formu */
        .cs-panel {
            padding-right: 12px;
            border-right: 1px dashed rgba(148, 163, 184, 0.4);
        }

        .cs-countdown {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
        }

        .cs-count-item {
            min-width: 70px;
            padding: 9px 8px;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(148, 163, 184, 0.55);
            text-align: center;
        }

        .cs-count-number {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: #e5f5ff;
        }

        .cs-count-label {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: var(--text-soft);
        }

        .cs-form-label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .18em;
            color: var(--text-soft);
            margin-bottom: 8px;
        }

        .cs-form-row {
            display: flex;
            gap: 8px;
        }

        .cs-input {
            flex: 1;
            padding: 10px 12px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.98);
            color: var(--text-main);
            font-size: 13px;
            outline: none;
        }

        .cs-input::placeholder {
            color: var(--text-mute);
        }

        .cs-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.45);
        }

        .cs-button {
            border: none;
            border-radius: 999px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(to right, var(--accent), var(--accent-2));
            color: #f9fafb;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
        }

        .cs-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.6);
            filter: brightness(1.05);
        }

        .cs-button span {
            font-size: 15px;
        }

        .cs-form-note {
            margin-top: 6px;
            font-size: 11px;
            color: var(--text-mute);
        }

        /* Sağ taraf – iletişim ve modül teaser */
        .cs-side {
            font-size: 13px;
            color: var(--text-soft);
        }

        .cs-contact {
            margin-bottom: 12px;
        }

        .cs-contact strong {
            font-weight: 600;
            color: #e5e7eb;
        }

        .cs-contact a {
            color: var(--accent);
            text-decoration: none;
        }

        .cs-contact a:hover {
            text-decoration: underline;
        }

        .cs-list-title {
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--text-mute);
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .cs-modules {
            list-style: none;
            font-size: 12px;
        }

        .cs-modules li {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 0;
        }

        .cs-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.8);
        }

        .cs-footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px dashed rgba(148, 163, 184, 0.4);
            font-size: 11px;
            color: var(--text-mute);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px;
        }

        .cs-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .cs-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 720px) {
            .cs-card {
                padding: 22px 18px 18px;
                border-radius: 18px;
            }

            .cs-title {
                font-size: 26px;
            }

            .cs-subtitle {
                font-size: 13px;
            }

            .cs-layout {
                grid-template-columns: minmax(0, 1fr);
                gap: 18px;
            }

            .cs-panel {
                padding-right: 0;
                border-right: none;
                border-bottom: 1px dashed rgba(148, 163, 184, 0.4);
                padding-bottom: 14px;
            }

            .cs-form-row {
                flex-direction: column;
            }

            .cs-button {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="cs-wrapper">
    <div class="cs-card">
        <div class="cs-header">
            <span class="cs-badge">
                <span class="cs-badge-dot"></span>
                TurkuazIT • Yakında Açılıyoruz
            </span>
            <h1 class="cs-title">Altyapınız için yeni nesil BT partneriniz geliyor.</h1>
            <p class="cs-subtitle">
                TurkuazIT modüler hizmet mimarisi ile; uzaktan destekten envanter yönetimine,
                ağ altyapısından güvenliğe kadar tüm ihtiyaçlarınızı tek çatı altında topluyoruz.
                Siteyi son dokunuşlara getiriyoruz.
            </p>
        </div>

        <div class="cs-layout">
            <!-- Sol taraf -->
            <div class="cs-panel">
                <div class="cs-countdown">
                    <div class="cs-count-item">
                        <span class="cs-count-number">07</span>
                        <span class="cs-count-label">Gün</span>
                    </div>
                    <div class="cs-count-item">
                        <span class="cs-count-number">12</span>
                        <span class="cs-count-label">Saat</span>
                    </div>
                    <div class="cs-count-item">
                        <span class="cs-count-number">45</span>
                        <span class="cs-count-label">Dakika</span>
                    </div>
                    <div class="cs-count-item">
                        <span class="cs-count-number">32</span>
                        <span class="cs-count-label">Saniye</span>
                    </div>
                </div>

                <form method="post" action="#" class="cs-form">
                    <label for="cs-email" class="cs-form-label">
                        Lansman ve kampanya duyurularını kaçırma
                    </label>
                    <div class="cs-form-row">
                        <input
                            type="email"
                            id="cs-email"
                            name="email"
                            class="cs-input"
                            placeholder="ornek@firma.com"
                            required
                        >
                        <button type="submit" class="cs-button">
                            <span>➜</span> Haberdar Et
                        </button>
                    </div>
                    <p class="cs-form-note">
                        E-posta adresin sadece lansman ve TurkuazIT duyuruları için kullanılacaktır.
                    </p>
                </form>
            </div>

            <!-- Sağ taraf -->
            <div class="cs-side">
                <div class="cs-contact">
                    <strong>İhtiyacın bekleyemeyecek kadar acil mi?</strong><br>
                    Hemen bizimle iletişime geç:
                    <br>
                    <a href="mailto:info@turkuazit.com">info@turkuazit.com</a><br>
                    <a href="tel:+905307074382">+90 (530) 707 43 82</a>
                </div>

                <div class="cs-modules-block">
                    <div class="cs-list-title">Modüler hizmet yapısından öne çıkanlar</div>
                    <ul class="cs-modules">
                        <li><span class="cs-dot"></span> M1 – Uzaktan Müdahale ve Teknik Destek</li>
                        <li><span class="cs-dot"></span> M3 – Kurumsal Ağ ve Network Altyapısı</li>
                        <li><span class="cs-dot"></span> M6 – Düzenli Teknik Kontrol ve Bakım</li>
                        <li><span class="cs-dot"></span> M11 – Donanım Envanteri ve Cihaz Etiketleme</li>
                        <li><span class="cs-dot"></span> M13 – Destek Kaydı ve Raporlama</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="cs-footer">
            <span>© <?php echo date('Y'); ?> TurkuazIT Bilişim Hizmetleri</span>
            <span>
                Ana sözleşme ve hizmet politikaları: 
                <a href="#">T-AHS-R1.0</a> · <a href="#">T-GHP-R1.0</a> · <a href="#">T-KVKK-TFP-SR1.0</a>
            </span>
        </div>
    </div>
</div>

</body>
</html>
