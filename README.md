# Beyn Case
## _Sipariş Modülü_

Mail ile iletmiş olduğunuz case projesinin dosyaları ve endpointleri bu dökümanda yeralmaktadır.

## Kullanılan Teknolojiler

- Laravel
- Rest Api
- Git
- PostMan

## Kurulum

```sh
git clone https://github.com/bilginnet/beyncase.git
cd beyncase
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

## REST API

Aşağıda mevcut endpointlerin listesi verilmiştir.
Daha detaylı kullanım için BeynCollection.postman_collection.json dosyasını postman üzerinde import ediniz.

| Action | Method | Url |
| ------ | ------ | ------ |
| Login | POST | localhost:8000/api/login |
| Create Order | POST | localhost:8000/api/order |
| Update Order | PUT | localhost:8000/api/order/5 |
| Show Order | GET | localhost:8000/api/order/5 |
| All Orders | GET | localhost:8000/api/order |

