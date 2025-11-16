# Flow Ön Muhasebe Uygulaması

Flask tabanlı bu örnek proje; stok takibi, basit gelir/gider kayıtları ve rol tabanlı yetkilendirme içeren web tabanlı bir ön muhasebe modülüdür.

## Başlangıç

```bash
python -m venv .venv
source .venv/bin/activate  # Windows için .venv\\Scripts\\activate
pip install -r requirements.txt
flask --app manage run --debug
```

İlk çalıştırmadan önce veritabanını oluşturmak için:

```bash
flask --app manage db init
flask --app manage db migrate -m "Initial tables"
flask --app manage db upgrade
```

## Yönetici Kullanıcısı Oluşturma

Python kabuğunda aşağıdaki komutları çalıştırarak yönetici kullanıcısı oluşturabilirsiniz:

```python
from app import create_app
from app.extensions import db
from app.models import User

app = create_app()
with app.app_context():
    admin = User(username="admin", role="admin")
    admin.set_password("admin123")
    db.session.add(admin)
    db.session.commit()
```

## Özellikler

- Rol tabanlı yetkilendirme (Yönetici, Sorumlu, Personel)
- Ürün kartı oluşturma ve düzenleme
- Stok alım/satım işlemleri ve otomatik stok güncellemesi
- Muhasebe defterine gelir/gider kaydı
- Kullanıcı yönetimi (sadece yönetici)
