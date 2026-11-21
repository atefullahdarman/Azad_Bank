# د بانک مدیریت سیسټم

دا د Laravel 12 او MySQL پر بنسټ د بانک د داخلي عملیاتو ویب اپلېکېشن دی. د XAMPP د محلي MySQL له لارې یې چلوئ.

## اړتیاوې

- XAMPP، PHP 8.2 یا لوړ او MySQL/MariaDB
- Composer
- Node.js او npm
- د PHP `pdo_mysql` extension فعاله وي

## ۱. پروژه خلاصه کړئ

ZIP فایل په یوه فولډر کې Extract کړئ. په PowerShell کې د پروژې اصلي فولډر ته لاړ شئ؛ هماغه فولډر چې `artisan` او `composer.json` پکې دي.

```powershell
cd C:\path\to\banking-system
```

## ۲. XAMPP کې ډیټابېس جوړ کړئ

په XAMPP Control Panel کې MySQL چالان کړئ. د phpMyAdmin د خلاصولو لپاره Apache هم چالانولای شئ، بیا `http://localhost/phpmyadmin` ته لاړ شئ او د `banking_system` په نوم نوی database جوړ کړئ، charset/collation یې `utf8mb4_unicode_ci` وټاکئ.

یا په phpMyAdmin د SQL ټب کې دا اجرا کړئ:

```sql
CREATE DATABASE banking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## ۳. د Laravel چاپېریال برابر کړئ

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

`.env` پرانیزئ او د XAMPP د MySQL معلومات برابر کړئ. د XAMPP عادي محلي نصب کې د `root` پاسورډ تش وي:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=banking_system
DB_USERNAME=root
DB_PASSWORD=
```

که د MySQL لپاره مو پاسورډ ټاکلی وي، `DB_PASSWORD` ته یې ولیکئ. `.env` شخصي فایل دی؛ له چا سره یې مه شریکوئ.

## ۴. جدولونه او مخکینۍ سرچینې جوړې کړئ

```powershell
php artisan migrate
npm install
npm run build
```

د دې کمانډونو له لارې د مشتریانو، حسابونو، معاملو، پورونو او کارنانو جدولونه په MySQL کې جوړېږي. پخوانی شخصي/نمونوي معلومات په ZIP کې نه دي شامل شوي.

## ۵. اپلېکېشن چالان کړئ

```powershell
php artisan serve
```

بیا په براوزر کې `http://127.0.0.1:8000` خلاص کړئ. د حساب جوړولو لپاره د ثبت پاڼې ته لاړ شئ. نوی ثبت شوی حساب په خوندي ډول د `customer` رول اخلي او د بانک داخلي پاڼې نه شي لیدلای.

که دا ستاسو د محلي نصب لومړنی مدیر وي، حساب ثبتولو وروسته د هماغه پروژې فولډر کې دا کمانډ اجرا کړئ او خپل د ثبت برېښنالیک ورکړئ:

```powershell
php artisan bank:user-role your-email@example.com admin
```

د کارکوونکي لپاره `employee` او د مشتری حساب ته د بېرته اړولو لپاره `customer` ولیکئ. مدیر د **Users** له پاڼې هم د نورو کاروونکو رولونه اداره کولای شي.

که پروژه له مخکې چلېده، د **Account Types** لپاره په هماغه ډیټابېس کې `php artisan migrate` یو ځل اجرا کړئ. پخواني حسابونه په **Unclassified** ډول ثبتېږي او ډول یې د **Accounts → Edit** له لارې بدلولای شئ.

## د هر ځل چالانولو طریقه

1. په XAMPP کې MySQL چالان کړئ.
2. د پروژې فولډر کې `php artisan serve` اجرا کړئ.
3. براوزر کې `http://127.0.0.1:8000` خلاص کړئ.

که د CSS یا JavaScript بدلونونه کوئ، په جلا PowerShell کړکۍ کې `npm run dev` چالان کړئ. د وروستي ZIP لپاره `npm run build` اړین دی.

## مهم یادښتونه

- ثبت شوي مشتریان د بانک د حسابونو او معاملو جدولونو ته لاسرسی نه لري. `admin` او `employee` رولونه ورته یوازې د مدیر له خوا ټاکل کېږي.
- د حسابونو، مشتریانو، معاملاتو او پورونو معلومات په MySQL کې ساتل کېږي.
- معامله تر تایید مخکې بیلانس نه بدلوي. تایید شوې پیسې د سنټونو پر بنسټ محاسبه کېږي، کم بیلانس لرونکی حساب منفي کېدای نه شي، او بشپړه شوې معامله حذف/بدلېدای نه شي.
- د یوه کارن درې ناسمې ننوتنې د ۱۵ دقیقو لپاره هغه کارن بندوي.
- Google Login د OAuth د `client ID` او `client secret` له برابرولو پرته فعالېدای نه شي.
- په دې محلي Laravel پرمختیايي سرور کې `php artisan serve` د ازموینې/پرمختیا لپاره دی؛ د عامه تولیدي بانکدارۍ خدمت لپاره جلا امنیتي، قانوني او د تادیاتو د تنظیماتو ارزونه پکار ده.
