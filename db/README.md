Veritabanı Değişiklikleri — TurkuazIT
=================================

Bu klasördeki SQL ve notlar, kullanıcı tablosunu (users) yeni alanlarla genişletmek için örnek içerir.

Önemli notlar:
- Bu bir örnek migration dosyasıdır: 20251130_add_user_fields.sql
- Bu değişiklikleri canlı veritabanınızda çalıştırmadan önce mutlaka yedek alın.
- Migration:
  - first_name, last_name: İsim/soyadı ayrı tutmak için
  - role: 'admin' | 'client' | 'staff1' | 'staff2' | 'staff3' — varsayılan kayıt rolü 'client'
  - is_dealer: bayi yetkisi (0/1)
  - is_active: hesabın aktif/pasif durumu
  - created_at, updated_at: kayıt zaman bilgisi

  user_profiles tablosu:
  - Bu tablo her kullanıcı için firma/adres/kargo bilgilerini tutar.
  - Tek kayıt (one-to-one) varsayıldı; user_id UNIQUE anahtar olarak konuldu.
  - fields: company_name, address_line1, address_line2, city, state, postal_code, country, phone, shipping_instructions

Veri migrasyonu önerisi:
- Eğer mevcut tablonuzda `full_name` bulunuyorsa, migration script basitçe first_name/last_name'ı ayırmayı dener.
- Karmaşık isimlerde (iki veya daha fazla isim olan kullanıcılar) sonuçların elle kontrol edilmesi gerekebilir.

Güvenlik ve pratik uygulamalar:
- Her zaman migration çalıştırmadan önce yedek al.
- Prod ortam için email doğrulama (token) ile kaydı tamamlama, parola sıfırlama ve rate limiting eklenmesi tavsiye edilir.

Ek: Missing column / Unknown column hataları ve migration çalıştırma
---------------------------------------------------------------
- Örnek bir hata: "Unknown column 'password_hash' in 'field list'" → Bu, `users` tablosunda `password_hash` sütununun olmadığını gösterir.
- Çözüm: Aşağıdaki migration dosyasını çalıştırarak eksik sütun(lar)ı ekleyin:
  - db/migrations/20251130_add_password_hash_and_required_columns.sql

PowerShell ile hızlı çalışma örneği (yerel geliştirme, mysql istemci yüklü ise):
```powershell
mysql -u root -p -h localhost turkuazit < c:\laragon\www\turkuazit\db\migrations\20251130_add_password_hash_and_required_columns.sql
```

NOT: Eğer MySQL sürümünüz `ADD COLUMN IF NOT EXISTS` desteklemiyorsa önce aşağıdaki sorguyla column varlığını kontrol edin ve varsa ALTER'ı atlayın:
```sql
SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
 WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'password_hash';
```

Eğer bu dosyayı çalıştıktan sonra da hata alırsanız `logs/register_errors.log` dosyasını kontrol edin — register handler server hatalarını buraya yazacak şekilde güncellendi.

Yeni migration'lar (admin envanter ve kullanıcı-firma ilişkilendirmesi):
- db/migrations/20251201_create_firmalar_and_user_fk.sql — `firmalar` tablosu oluşturulur ve `users.firma_id` alanı eklenir. Canlıda uygulamadan önce yedek alın ve staging ortamında test edin.

Admin envanter CRUD (lokasyonlar/firmalar/katmanların çalışması için):
- templates/admin_envanter_ekle.php
- templates/admin_envanter_liste.php
- templates/admin_envanter_edit.php
- admin/envanter_save.php
- admin/envanter_update.php
- admin/envanter_delete.php

Uploads: envanter dosyaları `uploads/envanter/{envanter_id}/` altına kaydedilir. Üretimde dosya izinleri ve boyut sınırlarını sunucu config ile kontrol edin.

