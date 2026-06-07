# 📍 LaraMap

Visualize your Laravel database like a map.

LaraMap is a Laravel package that helps you explore your database structure, models, and relationships directly from the terminal using Artisan commands. It is designed to make understanding Laravel projects fast and simple.

---

## 🚀 Installation

Install via Composer:

```bash
composer require lafnager-dev/laramap
```

---

## ⚙️ Usage

After installation, you can use the following commands:

### Display all model relationships

```bash
php artisan laramap:relations
```

**Example output:**

```
Users
 ├── hasMany Orders
 └── hasOne Profile

Orders
 ├── belongsTo User
 └── hasMany OrderItems

Products
 └── belongsTo Category
```

### Inspect a specific table

```bash
php artisan laramap:show users
```

**Example output:**

```
Table: users

Columns:
- id
- name
- email
- password
- created_at
- updated_at

Relations:
- hasMany Orders
- hasOne Profile
```

---

## 🧠 How it works

LaraMap scans your Laravel project automatically by reading Eloquent models inside `app/Models`. It detects relationships such as `hasOne`, `hasMany`, `belongsTo`, and `belongsToMany`, then builds a relationship map and displays it in a clean CLI tree format.

---

## 📁 Requirements

- PHP 8.3 or higher
- Laravel 10, 11, 12, or 13

---

## 💡 Features

- ⚡ Fast Laravel model scanning
- 🌲 Clean CLI tree output
- 🔒 Read-only mode (no database changes)
- 🔧 Works in any Laravel project

---

## 📦 Package Structure

```
src/
 ├── Console/
 │    ├── RelationsCommand.php
 │    ├── ShowCommand.php
 │
 ├── Scanner/
 │    ├── ModelScanner.php
 │    ├── RelationScanner.php
 │    ├── TableScanner.php
 │
 └── LaramapServiceProvider.php
```

---

## 📌 Roadmap

Future improvements planned:

- [ ] Laravel relationship path finder (`laramap:path User Product`)
- [ ] Graph visualization mode
- [ ] Migration tracking support
- [ ] JSON export feature
- [ ] Interactive TUI mode

---

## 🤝 Contributing

Pull requests are welcome. Feel free to improve scanning logic or add new features.
