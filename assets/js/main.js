// assets/js/main.js
// ═══════════════════════════════════════════════════════════════════════════
// TurkuazIT - Ana JavaScript Dosyası
// ═══════════════════════════════════════════════════════════════════════════
// Bu dosya tüm sayfalarda ortak kullanılan JavaScript işlevlerini içerir.
//
// İÇERİK:
// 1. Dosya yükleme önizlemesi - Envanter formlarında dosya adı gösterimi
// 2. Boş arama engelleme - Arama formlarında validasyon
// 3. Kayan Panel Sistemi - Sayfa yenilenmeden içerik geçişleri
// 4. Alt Panel Sistemi - Ana paneller içinde sekme geçişleri
// 5. Overview Dashboard - Canlı saat, sparkline grafikleri, global arama
// ═══════════════════════════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", () => {
  console.log("TurkuazIT front-end hazır.");

  // ─────────────────────────────────────────────────────────────────────
  // 0. HEADER KULLANICI DROPDOWN MENÜSÜ
  // ─────────────────────────────────────────────────────────────────────
  // Kullanıcı avatarına tıklandığında açılan dropdown menüyü yönetir.
  initUserDropdown();

  // ─────────────────────────────────────────────────────────────────────
  // 1. DOSYA YÜKLEME ÖNİZLEMESİ
  // ─────────────────────────────────────────────────────────────────────
  // Admin envanter ekle/düzenle sayfalarında dosya seçildiğinde
  // dosya adı ve boyutunu gösteren bir önizleme alanı oluşturur.
  // Maksimum 10 dosya gösterilir.
  document.querySelectorAll("input[type=file]").forEach((inp) => {
    inp.addEventListener("change", (e) => {
      const files = e.target.files;
      let container = inp.nextElementSibling;

      // Önizleme alanı yoksa oluştur
      if (!container || !container.classList.contains("file-preview")) {
        container = document.createElement("div");
        container.classList.add("file-preview");
        container.style.marginTop = ".5rem";
        inp.parentNode.insertBefore(container, inp.nextSibling);
      }

      // Önceki içeriği temizle ve yeni dosyaları listele
      container.innerHTML = "";
      Array.from(files)
        .slice(0, 10)
        .forEach((f) => {
          const el = document.createElement("div");
          el.style.fontSize = ".85rem";
          el.textContent = f.name + " (" + Math.round(f.size / 1024) + " KB)";
          container.appendChild(el);
        });
    });
  });

  // ─────────────────────────────────────────────────────────────────────
  // 2. BOŞ ARAMA ENGELLEMESİ
  // ─────────────────────────────────────────────────────────────────────
  // Arama formlarında boş değerle submit yapılmasını engeller.
  // Kullanıcıya uyarı mesajı gösterir.
  document.querySelectorAll("form input[name=q]").forEach((i) => {
    i.closest("form").addEventListener("submit", (e) => {
      if (!i.value.trim()) {
        e.preventDefault();
        alert("Lütfen arama terimi girin.");
      }
    });
  });

  // ─────────────────────────────────────────────────────────────────────
  // 3. KAYAN PANEL SİSTEMİNİ BAŞLAT
  // ─────────────────────────────────────────────────────────────────────
  // Yönetim panelinde sekme butonlarına tıklandığında
  // içerik panellerinin kayarak değişmesini sağlar.
  initSlidingPanels();

  // ─────────────────────────────────────────────────────────────────────
  // 4. ALT PANEL SİSTEMİNİ BAŞLAT
  // ─────────────────────────────────────────────────────────────────────
  // Kullanıcılar, Firmalar, Kategoriler gibi ana paneller içinde
  // Liste/Ekle alt görünümleri arasında geçiş sağlar.
  initSubPanels();

  // ─────────────────────────────────────────────────────────────────────
  // 5. OVERVIEW DASHBOARD ÖZELLİKLERİNİ BAŞLAT
  // ─────────────────────────────────────────────────────────────────────
  // Canlı saat, sparkline grafikleri, global arama ve diğer
  // dinamik dashboard özelliklerini başlatır.
  initOverviewDashboard();
});

// ═══════════════════════════════════════════════════════════════════════════
// HEADER KULLANICI DROPDOWN MENÜSÜ (initUserDropdown)
// ═══════════════════════════════════════════════════════════════════════════
// Sağ üstteki kullanıcı avatarına tıklandığında açılan dropdown menüyü yönetir.
// Menü dışına tıklandığında veya ESC tuşuna basıldığında menü kapanır.
// ═══════════════════════════════════════════════════════════════════════════
function initUserDropdown() {
  const userMenuBtn = document.getElementById("userMenuBtn");
  const userMenu = userMenuBtn?.closest(".site-header__user-menu");

  if (!userMenuBtn || !userMenu) return;

  // Toggle dropdown on button click
  userMenuBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    userMenu.classList.toggle("is-open");
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!userMenu.contains(e.target)) {
      userMenu.classList.remove("is-open");
    }
  });

  // Close dropdown on ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      userMenu.classList.remove("is-open");
    }
  });

  // Handle hash-based navigation for panel switching
  const dropdownItems = userMenu.querySelectorAll(
    '.site-header__dropdown-item[href*="#"]'
  );
  dropdownItems.forEach((item) => {
    item.addEventListener("click", (e) => {
      const href = item.getAttribute("href");
      if (href && href.includes("#")) {
        // Close dropdown after click
        userMenu.classList.remove("is-open");
      }
    });
  });
}

// ═══════════════════════════════════════════════════════════════════════════
// OVERVIEW DASHBOARD İŞLEVLERİ (initOverviewDashboard)
// ═══════════════════════════════════════════════════════════════════════════
// Genel bakış panelindeki dinamik özellikleri yönetir:
// - Canlı tarih/saat gösterimi
// - Sparkline mini grafikler
// - Global arama (klavye kısayolu dahil)
// - İpucu kapatma butonu
// ═══════════════════════════════════════════════════════════════════════════
function initOverviewDashboard() {
  // ─────────────────────────────────────────────────────────────────────
  // 5.1 CANLI SAAT GÖSTERİMİ
  // ─────────────────────────────────────────────────────────────────────
  // Her saniye güncellenen saat ve tarih gösterimi.
  const timeElement = document.getElementById("liveTime");
  const dateElement = document.getElementById("liveDate");

  if (timeElement || dateElement) {
    // Türkçe ay ve gün isimleri
    const months = [
      "Ocak",
      "Şubat",
      "Mart",
      "Nisan",
      "Mayıs",
      "Haziran",
      "Temmuz",
      "Ağustos",
      "Eylül",
      "Ekim",
      "Kasım",
      "Aralık",
    ];
    const days = [
      "Pazar",
      "Pazartesi",
      "Salı",
      "Çarşamba",
      "Perşembe",
      "Cuma",
      "Cumartesi",
    ];

    function updateDateTime() {
      const now = new Date();

      if (timeElement) {
        const hours = String(now.getHours()).padStart(2, "0");
        const minutes = String(now.getMinutes()).padStart(2, "0");
        timeElement.textContent = `${hours}:${minutes}`;
      }

      if (dateElement) {
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const dayName = days[now.getDay()];
        dateElement.textContent = `${day} ${month} ${year}, ${dayName}`;
      }
    }

    // Başlangıçta ve her dakika güncelle
    updateDateTime();
    setInterval(updateDateTime, 60000);
  }

  // ─────────────────────────────────────────────────────────────────────
  // 5.2 SPARKLİNE GRAFİKLERİ
  // ─────────────────────────────────────────────────────────────────────
  // İstatistik kartlarındaki mini trend grafikleri.
  // SVG path ile çizilen basit çizgi grafik.
  document.querySelectorAll(".metric-card__sparkline").forEach((container) => {
    const values = container.dataset.values;
    if (!values) return;

    const data = values.split(",").map((v) => parseInt(v) || 0);
    const svg = container.querySelector("svg");
    if (!svg || data.length === 0) return;

    // Grafik boyutları
    const width = 100;
    const height = 30;
    const padding = 2;

    // Veri normalizasyonu
    const max = Math.max(...data, 1);
    const min = Math.min(...data, 0);
    const range = max - min || 1;

    // Path oluştur
    const points = data.map((val, i) => {
      const x = padding + (i / (data.length - 1 || 1)) * (width - padding * 2);
      const y =
        height - padding - ((val - min) / range) * (height - padding * 2);
      return `${x},${y}`;
    });

    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("d", `M ${points.join(" L ")}`);
    path.setAttribute("fill", "none");
    path.setAttribute("stroke-linecap", "round");
    path.setAttribute("stroke-linejoin", "round");
    svg.appendChild(path);
  });

  // ─────────────────────────────────────────────────────────────────────
  // 5.3 GLOBAL ARAMA (Klavye Kısayolu)
  // ─────────────────────────────────────────────────────────────────────
  // "/" tuşuna basıldığında arama çubuğuna odaklanır.
  const searchInput = document.getElementById("globalSearchInput");

  if (searchInput) {
    // Klavye kısayolu: /
    document.addEventListener("keydown", (e) => {
      // Input veya textarea içinde değilsek ve "/" tuşuna basıldıysa
      if (
        e.key === "/" &&
        !["INPUT", "TEXTAREA"].includes(document.activeElement.tagName)
      ) {
        e.preventDefault();
        searchInput.focus();
      }

      // ESC tuşu ile arama çubuğundan çık
      if (e.key === "Escape" && document.activeElement === searchInput) {
        searchInput.blur();
      }
    });

    // Arama input değişikliği
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.trim();
      // TODO: AJAX ile arama sonuçlarını getir
      console.log("Arama sorgusu:", query);
    });
  }

  // ─────────────────────────────────────────────────────────────────────
  // 5.4 İPUCU KAPATMA BUTONU
  // ─────────────────────────────────────────────────────────────────────
  const tipsCloseBtn = document.querySelector(".overview-tips__close");
  if (tipsCloseBtn) {
    tipsCloseBtn.addEventListener("click", () => {
      const tipsCard = tipsCloseBtn.closest(".overview-tips-card");
      if (tipsCard) {
        tipsCard.style.opacity = "0";
        tipsCard.style.transform = "translateY(-10px)";
        setTimeout(() => {
          tipsCard.style.display = "none";
        }, 300);
      }
    });
  }

  // ─────────────────────────────────────────────────────────────────────
  // 5.5 METRİK KARTLARI ENTER TUŞU DESTEĞİ
  // ─────────────────────────────────────────────────────────────────────
  // Erişilebilirlik için: Enter veya Space ile kart tıklanabilir.
  document.querySelectorAll('.metric-card[role="button"]').forEach((card) => {
    card.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        card.click();
      }
    });
  });

  // ─────────────────────────────────────────────────────────────────────
  // 5.6 BEKLEYEN GÖREVLER ENTER TUŞU DESTEĞİ
  // ─────────────────────────────────────────────────────────────────────
  document.querySelectorAll('.pending-task[role="button"]').forEach((task) => {
    task.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        task.click();
      }
    });
  });
}

// ═══════════════════════════════════════════════════════════════════════════
// KAYAN PANEL SİSTEMİ (initSlidingPanels)
// ═══════════════════════════════════════════════════════════════════════════
// Sayfa yenilenmeden içerik bölümleri arasında yumuşak geçiş sağlar.
//
// NASIL ÇALIŞIR:
// 1. Sekme butonlarına (.panel-tab-btn) tıklama olayı dinlenir
// 2. Hedef panelin sırası belirlenir (data-panel özelliğinden)
// 3. Kayma yönü hesaplanır (sola mı sağa mı)
// 4. CSS animasyon sınıfları eklenir (slide-out-left, slide-in-right vb.)
// 5. Animasyon bitince sınıflar temizlenir
//
// ÖNEMLİ:
// - Animasyon süresi 400ms (CSS ile senkronize)
// - Aynı anda sadece bir animasyon çalışır (isAnimating kontrolü)
// - Container yüksekliği sabit tutularak layout atlama önlenir
// ═══════════════════════════════════════════════════════════════════════════
function initSlidingPanels() {
  // DOM elementlerini seç
  const tabButtons = document.querySelectorAll(".panel-tab-btn");
  const panels = document.querySelectorAll(".sliding-panel");
  const container = document.querySelector(".sliding-panel-container");

  // Gerekli elementler yoksa çık
  if (!tabButtons.length || !panels.length) return;

  // ─────────────────────────────────────────────────────────────────────
  // DEĞİŞKENLER
  // ─────────────────────────────────────────────────────────────────────
  // panelOrder: Panel sıralaması (kayma yönünü belirlemek için)
  // currentPanelIndex: Şu an görünen panelin indeksi
  // isAnimating: Animasyon kilidi (çift tıklama önleme)
  const panelOrder = Array.from(panels).map((p) => p.dataset.panelId);
  let currentPanelIndex = 0;
  let isAnimating = false;

  // ─────────────────────────────────────────────────────────────────────
  // BAŞLANGIÇ YÜKSEKLİĞİNİ AYARLA
  // ─────────────────────────────────────────────────────────────────────
  // Aktif panelin yüksekliğine göre container minimum yüksekliği belirlenir.
  // Bu sayede panel değiştiğinde layout atlaması önlenir.
  const activePanel = document.querySelector(".sliding-panel.is-active");
  if (activePanel && container) {
    container.style.minHeight = activePanel.offsetHeight + "px";
  }

  // ─────────────────────────────────────────────────────────────────────
  // SEKME BUTONLARINA TIKLA OLAYINI EKLE
  // ─────────────────────────────────────────────────────────────────────
  tabButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      // Animasyon devam ediyorsa yeni tıklamayı engelle
      if (isAnimating) return;

      // Hedef panel bilgilerini al
      const targetPanelId = btn.dataset.panel;
      const targetPanelIndex = panelOrder.indexOf(targetPanelId);

      // Aynı sekmeye tıklandıysa işlem yapma
      if (targetPanelIndex === currentPanelIndex) return;

      // Animasyon kilidini etkinleştir
      isAnimating = true;

      // ─────────────────────────────────────────────────────────────
      // KAYMA YÖNÜNÜ BELİRLE
      // Hedef panel şu anki panelden sonra ise: sola kay
      // Hedef panel şu anki panelden önce ise: sağa kay
      // ─────────────────────────────────────────────────────────────
      const slideDirection =
        targetPanelIndex > currentPanelIndex ? "left" : "right";

      // ─────────────────────────────────────────────────────────────
      // BUTON DURUMLARINI GÜNCELLE
      // Tüm butonlardan aktif sınıfını kaldır, tıklanan butona ekle.
      // Stil sınıflarını da güncelle (btn--primary <-> btn--ghost)
      // ─────────────────────────────────────────────────────────────
      tabButtons.forEach((b) => {
        b.classList.remove("is-active");
        if (b.classList.contains("btn--primary")) {
          b.classList.remove("btn--primary");
          b.classList.add("btn--ghost");
        }
      });
      btn.classList.add("is-active");
      btn.classList.remove("btn--ghost");
      btn.classList.add("btn--primary");

      // ─────────────────────────────────────────────────────────────
      // PANELLERİ SEÇ
      // Şu an aktif olan ve geçiş yapılacak paneli bul
      // ─────────────────────────────────────────────────────────────
      const currentPanel = document.querySelector(".sliding-panel.is-active");
      const targetPanel = document.querySelector(
        `[data-panel-id="${targetPanelId}"]`
      );

      if (!currentPanel || !targetPanel) {
        isAnimating = false;
        return;
      }

      // ─────────────────────────────────────────────────────────────
      // CONTAINER YÜKSEKLİĞİNİ AYARLA
      // Her iki panelin yüksekliğinden büyük olanı kullan.
      // Bu sayede geçiş sırasında layout atlamaz.
      // ─────────────────────────────────────────────────────────────
      if (container) {
        const currentHeight = currentPanel.offsetHeight;
        const targetHeight = targetPanel.offsetHeight;
        container.style.minHeight =
          Math.max(currentHeight, targetHeight) + "px";
      }

      // ─────────────────────────────────────────────────────────────
      // HEDEF PANELİ ANİMASYONA HAZIRLA
      // Absolute positioning ile mevcut panelin üstüne konumlandır.
      // Görünür yap ve animasyon sınıfını ekle.
      // ─────────────────────────────────────────────────────────────
      targetPanel.style.position = "absolute";
      targetPanel.style.top = "0";
      targetPanel.style.left = "0";
      targetPanel.style.width = "100%";
      targetPanel.classList.add("is-active");

      // ─────────────────────────────────────────────────────────────
      // ANİMASYON SINIFLARINI EKLE
      // Kayma yönüne göre uygun CSS animasyonlarını uygula.
      // ─────────────────────────────────────────────────────────────
      if (slideDirection === "left") {
        // Sola kayarak çıkış, sağdan giriş
        currentPanel.classList.add("slide-out-left");
        targetPanel.classList.add("slide-in-right");
      } else {
        // Sağa kayarak çıkış, soldan giriş
        currentPanel.classList.add("slide-out-right");
        targetPanel.classList.add("slide-in-left");
      }

      // ─────────────────────────────────────────────────────────────
      // ANİMASYON BİTİNCE TEMİZLİK YAP
      // 400ms sonra (CSS animasyon süresi) sınıfları temizle.
      // Container yüksekliğini yeni panele göre ayarla.
      // ─────────────────────────────────────────────────────────────
      setTimeout(() => {
        // Animasyon sınıflarını kaldır
        currentPanel.classList.remove(
          "is-active",
          "slide-out-left",
          "slide-out-right"
        );
        targetPanel.classList.remove("slide-in-right", "slide-in-left");

        // Hedef panelin pozisyonunu sıfırla (relative'e dön)
        targetPanel.style.position = "";
        targetPanel.style.top = "";
        targetPanel.style.left = "";
        targetPanel.style.width = "";

        // Container yüksekliğini yeni panele göre güncelle
        if (container) {
          container.style.minHeight = targetPanel.offsetHeight + "px";
        }

        // Durum değişkenlerini güncelle
        currentPanelIndex = targetPanelIndex;
        isAnimating = false;
      }, 400); // CSS animasyon süresi ile eşleşmeli
    });
  });

  // ─────────────────────────────────────────────────────────────────────
  // HIZLI GEÇİŞ BUTONLARI (data-goto-panel)
  // ─────────────────────────────────────────────────────────────────────
  // İstatistik kartları, hızlı erişim butonları vb. öğelerden
  // doğrudan panel geçişi yapılmasını sağlar.
  // Bu butonlar ilgili sekme butonunu programatik olarak tıklar.
  function goToPanel(targetPanelId) {
    const targetBtn = document.querySelector(
      `.panel-tab-btn[data-panel="${targetPanelId}"]`
    );
    if (targetBtn) {
      targetBtn.click();
    }
  }

  // Tüm data-goto-panel özelliği olan öğelere tıklama olayı ekle
  document.querySelectorAll("[data-goto-panel]").forEach((el) => {
    el.addEventListener("click", (e) => {
      // Eğer bir link değilse default davranışı engelle
      if (el.tagName !== "A") {
        e.preventDefault();
      }
      const targetPanelId = el.dataset.gotoPanel;
      goToPanel(targetPanelId);
    });
  });

  // ─────────────────────────────────────────────────────────────────────
  // PENCERE BOYUTU DEĞİŞTİĞİNDE YÜKSEKLİĞİ GÜNCELLE
  // ─────────────────────────────────────────────────────────────────────
  // Responsive tasarımda panel yükseklikleri değişebilir.
  // Debounce ile gereksiz hesaplama önlenir.
  let resizeTimeout;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      const activePanel = document.querySelector(".sliding-panel.is-active");
      if (activePanel && container) {
        container.style.minHeight = activePanel.offsetHeight + "px";
      }
    }, 100);
  });
}

// ═══════════════════════════════════════════════════════════════════════════
// ALT PANEL SİSTEMİ (initSubPanels)
// ═══════════════════════════════════════════════════════════════════════════
// Ana paneller içindeki ikincil sekme geçişlerini yönetir.
// Örneğin: Kullanıcılar panelinde "Kullanıcı Listesi" ve "Kullanıcı Ekle"
// arasında geçiş yapılmasını sağlar.
//
// NASIL ÇALIŞIR:
// 1. Alt sekme butonlarına (.sub-panel-tab) tıklama olayı dinlenir
// 2. Her sekme grubu data-sub-panel-group ile tanımlanır
// 3. Hedef alt panel data-sub-panel özelliğinden belirlenir
// 4. Aktif sınıfları güncelleyerek görünüm değiştirilir
// 5. Fade-in animasyonu CSS ile uygulanır
// ═══════════════════════════════════════════════════════════════════════════
function initSubPanels() {
  // ─────────────────────────────────────────────────────────────────────
  // ALT SEKME BUTONLARINA TIKLAMA OLAYI EKLE
  // ─────────────────────────────────────────────────────────────────────
  // Her alt sekme butonu tıklandığında ilgili alt paneli aktif yapar.
  // Aynı gruptaki diğer sekmeleri deaktif eder.
  document.querySelectorAll(".sub-panel-tab").forEach((tab) => {
    tab.addEventListener("click", (e) => {
      e.preventDefault();

      // Hedef alt panel ID'sini al
      const targetSubPanelId = tab.dataset.subPanel;

      // Sekme grubunu bul (parent container)
      const tabContainer = tab.closest(".sub-panel-tabs");
      if (!tabContainer) return;

      // Panel kart konteynerini bul
      const panelCard = tab.closest(".panel-card");
      if (!panelCard) return;

      // ─────────────────────────────────────────────────────────────
      // SEKME BUTONLARINI GÜNCELLE
      // Gruptaki tüm butonlardan aktif sınıfını kaldır,
      // tıklanan butona ekle.
      // ─────────────────────────────────────────────────────────────
      tabContainer.querySelectorAll(".sub-panel-tab").forEach((t) => {
        t.classList.remove("is-active");
      });
      tab.classList.add("is-active");

      // ─────────────────────────────────────────────────────────────
      // ALT PANELLERİ GÜNCELLE
      // Karttaki tüm alt panellerden aktif sınıfını kaldır,
      // hedef alt panele ekle.
      // ─────────────────────────────────────────────────────────────
      panelCard.querySelectorAll(".sub-panel").forEach((p) => {
        p.classList.remove("is-active");
      });

      const targetSubPanel = panelCard.querySelector(
        `[data-sub-panel-id="${targetSubPanelId}"]`
      );
      if (targetSubPanel) {
        targetSubPanel.classList.add("is-active");
      }
    });
  });

  // ─────────────────────────────────────────────────────────────────────
  // ALT PANEL HIZLI GEÇİŞ BUTONLARI (data-sub-panel-goto)
  // ─────────────────────────────────────────────────────────────────────
  // "İlk Kullanıcıyı Ekle", "İptal" gibi butonlardan doğrudan
  // alt panel geçişi yapılmasını sağlar.
  document.querySelectorAll("[data-sub-panel-goto]").forEach((el) => {
    el.addEventListener("click", (e) => {
      // Link değilse default davranışı engelle
      if (el.tagName !== "A") {
        e.preventDefault();
      }

      const targetSubPanelId = el.dataset.subPanelGoto;
      const groupName = el.dataset.subPanelGroup;

      // Hedef grubu ve sekmeyi bul
      let targetTab;
      if (groupName) {
        // Grup adı verilmişse o gruptaki sekmeyi bul
        const tabContainer = document.querySelector(
          `[data-sub-panel-group="${groupName}"]`
        );
        if (tabContainer) {
          targetTab = tabContainer.querySelector(
            `[data-sub-panel="${targetSubPanelId}"]`
          );
        }
      } else {
        // Grup adı yoksa en yakın panel kartındaki sekmeyi bul
        const panelCard = el.closest(".panel-card");
        if (panelCard) {
          targetTab = panelCard.querySelector(
            `[data-sub-panel="${targetSubPanelId}"]`
          );
        }
      }

      // Hedef sekmeyi programatik olarak tıkla
      if (targetTab) {
        targetTab.click();
      }
    });
  });
}
