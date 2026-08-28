# Laravel Multi-Vendor E-Commerce

AI-powered multi-vendor e-commerce marketplace built with Laravel 10 and MySQL, featuring dedicated customer, seller, and admin portals, complete product and order management, and a Gemini-powered shopping assistant that understands customer needs and recommends relevant, clickable products from the live catalog.

## Project preview

## Main features

### Customer and storefront

- Customer registration, login, logout, profile, and password management
- Marketplace homepage and vendor-aware product catalog
- Product search, category filtering, sorting, and pagination
- Product details with images, seller/shop information, price, and stock availability
- Session-based shopping cart with quantity updates and item removal
- Authenticated checkout with cash on delivery (COD)
- Customer order history and order-detail pages
- Wishlist management
- Product reviews and ratings
- AI-assisted product discovery with clickable recommendations

### Vendor/seller area

- Separate seller registration, verification, login, password reset, and dashboard
- Seller profile and profile-image management
- Shop setup and shop settings
- Seller-owned product creation, editing, listing, and deletion
- Category and subcategory assignment
- Product pricing, inventory, visibility, and stock management
- Multiple product-image upload and deletion
- Vendor-specific order listing and order details
- Vendor control over the status of their own order items

### Administrator area

- Separate administrator authentication and password reset
- Admin profile and profile-image management
- General marketplace settings, logo, and favicon management
- Category and subcategory management
- Marketplace-wide order listing and order details
- Order-item status management
- Payment-status management

## Technology stack

- PHP 8.1 or later
- Laravel 10
- MySQL
- Blade templates
- Livewire 3
- JavaScript and Vite
- Laravel HTTP client / Guzzle
- Gemini API
- Laravel Herd (recommended for local Windows development)
- TablePlus or another MySQL client (optional)

## Local installation

> These commands are for a new clone on another computer. If the project is already running locally, you do not need to reinstall it.

### 1. Clone the repository

```bash
git clone https://github.com/hirenvaddoriya2001/laravel-Marketplace-Multivendor-ecommerce
cd YOUR_REPOSITORY
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Create the environment file

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

On macOS or Linux:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 4. Create and configure MySQL

Create an empty MySQL database, for example:

```sql
CREATE DATABASE laravecom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update the database section in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravecom
DB_USERNAME=root
DB_PASSWORD=
```

Use the MySQL username and password configured on your own computer. Do not commit `.env`.

### 5. Create the database tables

```bash
php artisan migrate
```

If the project contains safe demo seeders, use:

```bash
php artisan migrate --seed
```

### 6. Prepare storage and frontend assets

```bash
php artisan storage:link
npm run build
php artisan optimize:clear
```

For frontend development with automatic rebuilding, use this instead of `npm run build`:

```bash
npm run dev
```

## Running on localhost

### Option A: Laravel Herd

1. Install and open Laravel Herd.
2. Add or park the parent directory containing the project.
3. Ensure Herd is using PHP 8.1 or later.
4. Start MySQL and confirm the `.env` database settings.
5. Open the `.test` URL shown by Herd, such as `http://your-project.test`.

The seller area is available at:

```text
/seller
```

The administrator login is available at:

```text
/admin/login
```

### Option B: Artisan development server

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

If Vite is running in development mode, keep this command open in another terminal:

```bash
npm run dev
```

## AI-assisted shopping chat

The homepage includes a Gemini-powered assistant that interprets a customer's natural-language request and recommends products from the marketplace catalog.



### Gemini configuration

Create an API key in [Google AI Studio](https://aistudio.google.com/), then add it only to your local `.env`:

```env
GEMINI_API_KEY=your_private_api_key
GEMINI_MODEL=gemini-3.7-flash
```

Clear cached configuration:

```bash
php artisan optimize:clear
```

Confirm Laravel can read the configuration without displaying the actual key:

```bash
php artisan tinker
```

```php
filled(config('services.gemini.key'));
config('services.gemini.model');
```

Exit Tinker:

```php
exit
```

Test the assistant with requests such as:

```text
I need an affordable product for my bedroom.
Show me an in-stock product under $100.
Help me choose a gift.
```

If an API request fails, inspect:

```text
storage/logs/laravel.log
```

### AI security notes

- Never commit `GEMINI_API_KEY` or expose it in browser JavaScript.
- Keep the chat route rate limit enabled.
- Send only the minimum product data needed for recommendations.
- Display AI text using `textContent`, not `innerHTML`.
- Treat AI output as advice and database values as authoritative.
- Revoke an API key immediately if it is exposed.


```powershell
New-Item -ItemType Directory -Force .\docs\screenshots
```



## Security and repository hygiene

The repository must not contain:

- `.env` or environment-specific secrets
- Gemini API keys
- MySQL passwords or database exports
- Real customer, seller, address, or order data
- Laravel log files
- `vendor` or `node_modules`

Before every push, review the staged files:

```bash
git status
```


## License

