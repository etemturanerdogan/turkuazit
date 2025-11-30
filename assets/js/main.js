// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('TurkuazIT front-end ready.');

    // Örneğin: mobil menü aç kapa fonksiyonu ileride buraya gelecek
    // const menuToggle = document.querySelector('[data-menu-toggle]');
    // const nav = document.querySelector('.nav');

    // Formlar: dosya upload preview (admin envanter ekle / düzenle sayfasında)
    document.querySelectorAll('input[type=file]').forEach(inp => {
        inp.addEventListener('change', (e) => {
            const files = e.target.files;
            let container = inp.nextElementSibling;
            // Eğer preview alanı yoksa basit bir oluştur
            if (!container || !container.classList.contains('file-preview')) {
                container = document.createElement('div');
                container.classList.add('file-preview');
                container.style.marginTop = '.5rem';
                inp.parentNode.insertBefore(container, inp.nextSibling);
            }
            container.innerHTML = '';
            Array.from(files).slice(0, 10).forEach(f => {
                const el = document.createElement('div');
                el.style.fontSize = '.85rem';
                el.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
                container.appendChild(el);
            });
        });
    });

    // Basit arama formlarında boş submit engellemesi
    document.querySelectorAll('form input[name=q]').forEach(i => {
        i.closest('form').addEventListener('submit', (e) => {
            if (!i.value.trim()) {
                e.preventDefault();
                // küçük uyarı (alert yerine daha güzel bir UI ekleyebilirsiniz)
                alert('Lütfen arama terimi girin.');
            }
        });
    });
});
