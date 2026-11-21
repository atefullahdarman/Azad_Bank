# Bank Management System

This web application uses Laravel 12 and MySQL to manage a bank’s internal operations. It runs locally using MySQL through XAMPP.

## Requirements

- XAMPP with PHP 8.2 or later and MySQL or MariaDB
- Composer
- Node.js and npm
- The PHP `pdo_mysql` extension enabled

## 1. Open the project

Extract the ZIP file into a folder. In PowerShell, navigate to the project’s root folder, which contains `artisan` and `composer.json`.

```powershell
cd C:\path\to\banking-system
```

## 2. Create the database in XAMPP

Start MySQL in the XAMPP Control Panel. To use phpMyAdmin, also start Apache and open [phpMyAdmin](http://localhost/phpmyadmin). Create a database named `banking_system`, with `utf8mb4` as the character set and `utf8mb4_unicode_ci` as the collation.

Alternatively, run the following in the phpMyAdmin **SQL** tab.

```sql
CREATE DATABASE banking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 3. Configure the Laravel environment

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Open `.env` and configure the connection to XAMPP’s MySQL server. In a default local XAMPP installation, the `root` password is blank.

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=banking_system
DB_USERNAME=root
DB_PASSWORD=
```

If you set a MySQL password, enter it in `DB_PASSWORD`. Keep your `.env` file private.

## 4. Create the database tables and build the frontend assets

```powershell
php artisan migrate
npm install
npm run build
```

The migration creates the customers, accounts, transactions, loans, and users tables in MySQL. The ZIP file contains no existing personal or sample data.

## 5. Start the application

```powershell
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser and go to the registration page to create a user account. New user accounts receive the `customer` role by default, which restricts access to the bank’s internal pages.

To set up the first administrator for your local installation, register an account, then run the following command from the project folder. Replace `your-email@example.com` with the email address you registered with.

```powershell
php artisan bank:user-role your-email@example.com admin
```

Use `employee` to assign the employee role or `customer` to switch an account back to the customer role. Administrators can also manage other users’ roles from the **Users** page.

If the application is already set up, run `php artisan migrate` once against the existing database to add **Account Types**. Existing bank accounts are assigned the **Unclassified** type. You can change the type under **Accounts → Edit**.

## Starting the application after setup

1. Start MySQL in XAMPP.
2. Run `php artisan serve` from the project folder.
3. Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

If you are editing CSS or JavaScript, run `npm run dev` in a separate PowerShell window. Run `npm run build` before packaging the final ZIP.

## Important notes

- Customer users are blocked from accessing the bank’s accounts and transactions tables. Only administrators can assign the `admin` and `employee` roles.
- Account, customer, transaction, and loan data is stored in MySQL.
- Transactions affect account balances only after approval. The application calculates approved amounts in cents and prevents transactions from overdrawing an account. Completed transactions are locked against editing and deletion.
- Three failed login attempts lock the user account for 15 minutes.
- Google Login requires an OAuth `client ID` and `client secret`.
- The local Laravel server started with `php artisan serve` is for development and testing. A public production banking service requires a separate review of security, legal requirements, and payment settings.
