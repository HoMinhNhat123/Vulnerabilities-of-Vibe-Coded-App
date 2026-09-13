# Community Forum

A simple PHP + MySQL community forum / blog. Lives under `GPT/GPT5.6 web1/`.

## Features

- Homepage with recent posts and keyword search
- User registration and login (username / password)
- Create, edit, and delete your own posts
- Comments on posts (logged-in users)
- Profile picture upload
- Admin page listing all registered users

## Requirements

- PHP 8+ with **either** `pdo_mysql` **or** `pdo_sqlite`
- MySQL/MariaDB (optional — SQLite is used automatically if MySQL is unavailable)

### Fixing “could not find driver”

That error means PHP has no MySQL PDO extension. Install one of:

```bash
# Ubuntu / Debian
sudo apt install php-mysql
# or SQLite-only
sudo apt install php-sqlite3

# macOS (Homebrew PHP)
brew install php
```

Then restart the PHP server. This app will also fall back to SQLite automatically when `pdo_mysql` is missing but `pdo_sqlite` is present.

## Setup

1. (Optional) For MySQL, create the database and user, or edit `config/database.php`:

```sql
CREATE DATABASE community_forum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'forum_user'@'localhost' IDENTIFIED BY 'forum_pass123';
GRANT ALL PRIVILEGES ON community_forum.* TO 'forum_user'@'localhost';
FLUSH PRIVILEGES;
```

2. From this directory, start PHP’s built-in server:

```bash
cd "GPT/GPT5.6 web1"
php -S localhost:8080
```

3. Open `http://localhost:8080/setup.php` once to create tables and the admin account.

4. Visit `http://localhost:8080/`.

### Default admin

- **Username:** `admin`
- **Password:** `admin123`

## Project layout

```
GPT/GPT5.6 web1/
  config/database.php   # DB credentials (+ SQLite fallback)
  data/                 # SQLite file (created when needed)
  includes/             # Shared auth, header, footer
  css/style.css
  js/main.js
  uploads/profiles/     # Profile pictures
  sql/schema.sql
  setup.php             # One-time schema setup
  index.php             # Home + search
  register.php / login.php / logout.php
  create_post.php / edit_post.php / delete_post.php
  post.php              # View post + comments
  profile.php           # Profile picture upload
  admin.php             # User list (admin only)
```
