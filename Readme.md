# Simple POS

A point of sale system for a small food outlet, built with vanilla PHP and MySQL.
An admin manages the catalogue and reads sales reports; a cashier rings up orders
and closes their till at the end of a shift.

This repository is coursework for **ISB37904 Software Quality and Configuration
Management** at UniKL MIIT. It continues an existing open source project rather
than starting from scratch, adding new features on top of a working codebase.

- **Upstream project:** [lenard123/simple-pos](https://github.com/lenard123/simple-pos)
  by Lenard Mangay-ayam. The original login, product, category and cashier
  screens are his work; his commit history is preserved here unchanged.

---

## Team and responsibilities

Each member was assigned a feature by the lecturer and is the person in charge
(PIC) of it.

| Person in charge | GitHub | Feature | Where it lives |
|---|---|---|---|
| Adam Haikal | [@DwZukii](https://github.com/DwZukii) | Manage users (admin) | `admin_account.php`, `api/user_controller.php` |
| Adam Haikal | [@DwZukii](https://github.com/DwZukii) | Generate report (admin) | `admin_sales.php` |
| Amirul | [@Amilul](https://github.com/Amilul) | Track which cashier created each order | `models/Order.php`, `api/cashier_controller.php` |
| Hadri | [@hadrihabibi](https://github.com/hadrihabibi) | Check daily cash sales (cashier) | `cashier_sales.php`, `models/Sales.php` |
| Adam Haikal | [@DwZukii](https://github.com/DwZukii) | Shift closing / cash handover (cashier) | `cashier_shift_report.php`, `models/ShiftReport.php` |

The "Generate report" feature was originally assigned to a fifth member who
became uncontactable, and was picked up by Adam.

Development was done together on one machine because of local setup problems on
the other laptops. Commits are authored by whoever owned the feature and
committed from the shared session, so `git log --format=fuller` shows a
different Author and Committer on some commits. That is accurate rather than a
mistake.

---

## Features

### Inherited from the upstream project
- Session based login with two roles, admin and cashier
- Product catalogue with categories
- Cashier register: add products to a cart, adjust quantities, take payment
- Basic sales totals for today and all time

### Added by this team
- **Manage users** — an admin can list, create and delete accounts and choose
  the role. New passwords are hashed with `password_hash()`.
- **Sales report with a date range** — filter takings between two dates, see the
  total for that period alongside today and all time, and print the result.
- **Per cashier order tracking** — every order records which cashier processed
  it, which is what makes the two cashier reports possible.
- **Daily cash sales** — a cashier sees their own takings for today and the
  orders behind that figure. Other cashiers' sales are not visible.
- **Shift closing / cash handover** — at the end of a shift the cashier counts
  the till and submits the figure. The system compares it against what their
  sales say should be there and records the difference as Balanced, Over or
  Short, with an optional note. Past handovers are listed.
- **Add stock** — the previously empty stock screen now works.
- **Role aware navigation** — admins and cashiers see different menus.

---

## Built with

| | |
|---|---|
| Language | PHP 8 (no framework) |
| Database | MySQL 8 (PDO, prepared statements) |
| Front end | Plain HTML/CSS, [Alpine.js](https://alpinejs.dev/) for the cart, simple-datatables for tables |
| Local server | [Laragon](https://laragon.org/) (Apache + MySQL + PHP) |

There is no Composer, no build step and no package manager. Clone it, point a
web server at it, import the database, and it runs.

---

## Getting started

### 1. Requirements
- [Laragon](https://laragon.org/) (or XAMPP, or any Apache + PHP 8 + MySQL 8 stack)
- [Git](https://git-scm.com/download/win)

### 2. Clone into the web root
```bash
cd C:/laragon/www
git clone https://github.com/DwZukii/simple-pos.git
```

Clone it, do not download the ZIP. A ZIP has no git history, so you cannot
commit, branch or open a pull request from it.

### 3. Create the database
Open your database tool (Laragon's **Database** button opens HeidiSQL, or use
phpMyAdmin) and create a database named exactly:

```
simple-pos
```

Then import `simple-pos.sql` into it.

### 4. Check the configuration
`_config.php` should match your local MySQL. The Laragon defaults already do:

```php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'simple-pos');
```

Do not commit a changed database name here. The name must stay `simple-pos`
because that is what `simple-pos.sql` declares and what everyone else uses.

### 5. Optional: load demo data
The bare schema has almost nothing in it, so every report looks empty. To get a
populated system for testing or a demonstration:

```bash
mysql -u root simple-pos < demo_data.sql
```

That seeds 19 products across 5 categories, about four weeks of orders from both
cashier accounts, and a history of shift handovers. Dates are relative to today,
so it always looks current. It leaves the `users` table alone and can be re-run.

### 6. Start it
Open Laragon and click **Start All**, then visit:

```
http://simple-pos.test
```

or `http://localhost/simple-pos` if you are not using Laragon's virtual hosts.

### Demo accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@gmail.com` | `adminadmin` |
| Cashier | `cashier@email.com` | `cashiercashier` |

These come from the seed file and are for local development only. Their
passwords are stored as plain text because they predate the hashing added in the
manage users feature; any account created through the app is hashed properly.

---

## Walkthrough

### As an admin

1. **Sign in** at `login.php` with the admin account. You land on the inventory
   screen.
2. **Inventory** (`admin_home.php`) lists every product with its category, stock
   and price, and lets you delete or edit one.
3. **Add Item** (`admin_add_item.php`) creates a product against a category.
4. **Category** (`admin_category.php`) adds, renames and deletes categories.
   Deleting a category also deletes its products.
5. **Add Stock** (`admin_add_stock.php`) picks a product and adds to its
   quantity, for restocking.
6. **Sales** (`admin_sales.php`) is the reporting screen. It shows today's
   takings, all time takings, and a total for a date range you choose. Pick a
   **From** and **To** date and press **Filter Sales** to see the transactions
   in that window; **Print Report** opens the browser print dialog.
7. **Account** (`admin_account.php`) lists users and creates new ones. Choose a
   name, email, password and role. You cannot delete the account you are
   currently signed in as.

### As a cashier

1. **Sign in** with the cashier account. You land on the register.
2. **POS Register** (`index.php`) lists the products on the left. Press **Add
   Product** to put one in the cart, then use **−** and **+** to change the
   quantity. The cart cannot exceed available stock.
3. Enter the cash received in **Payment**; the change is worked out as you type.
4. Press **Process Order**. Stock is reduced, the sale is recorded against your
   account, and a confirmation appears. If the cart would oversell a product the
   order is refused with a message saying how many are left.
5. **Shift Sales** (`cashier_sales.php`) shows your own takings for today and
   the orders behind them. You do not see other cashiers' sales.
6. **Close Shift** (`cashier_shift_report.php`) shows what should be in the till
   based on your sales. Count the cash, enter the figure, add a note if it does
   not match, and submit. The handover is recorded as Balanced, Over or Short,
   and appears in your history below.

---

## Project structure

```
simple-pos/
├── _config.php          database credentials, role and flash message constants
├── _init.php            loads everything, opens the PDO connection, starts the session
├── _guards.php          access checks (adminOnly, cashierOnly, isAdmin, ...)
├── _helper.php          get/post readers, redirect, flash messages
│
├── login.php            sign in
├── index.php            cashier register
├── cashier_sales.php    cashier's own daily takings
├── cashier_shift_report.php   cash handover
├── admin_home.php       product list
├── admin_add_item.php   create product
├── admin_update_item.php  edit product
├── admin_add_stock.php  restock
├── admin_category.php   categories
├── admin_sales.php      sales report
├── admin_account.php    manage users
│
├── api/                 form handlers, one per area
├── models/              one class per table, plain PDO
├── templates/           shared header and navigation
├── css/  js/            styles and Alpine/datatable scripts
│
├── simple-pos.sql       schema and seed accounts
└── demo_data.sql        optional demo catalogue and trading history
```

There is no router. Each page is its own PHP file, guards itself at the top,
loads what it needs from `models/`, and posts to a handler in `api/`.

---

## Database

| Table | Holds |
|---|---|
| `users` | accounts, with `role` of `ADMIN` or `CASHIER` |
| `categories` | product groupings |
| `products` | catalogue with `quantity` (stock) and `price` |
| `orders` | one row per completed sale, with `user_id` for the cashier |
| `order_items` | the lines of an order, with the price at time of sale |
| `shift_reports` | cash handovers: expected, counted, variance and notes |

Prices on `order_items` are stored per line rather than read from `products`, so
historical sales keep the price they were actually sold at when the catalogue
price changes later.

### Changing the schema
There is no migration tool. `simple-pos.sql` is a snapshot, and it does not
update anyone's existing database by itself. If a change touches the schema,
say so in the pull request and include the `ALTER TABLE` statements so everyone
can apply them to their own local database after pulling.

---

## Development workflow

`main` is protected: it needs a pull request with one approval, and nobody can
push to it directly.

```bash
git checkout main && git pull
git checkout -b feature/your-task-name

# work, committing in small logical steps

git push -u origin feature/your-task-name
```

Then open a pull request against `main`, get a teammate to review it (GitHub
will not let you approve your own), and merge. Delete the branch afterwards and
pull `main` again before starting the next one.

Prefix branches with `feature/`, `fix/`, `docs/` or `chore/`.

---

## Known limitations

Recorded honestly rather than hidden.

- **Stock is checked per checkout, not across simultaneous ones.** Two cashiers
  selling the last item at the same instant could still both succeed. Fixing it
  properly needs a locking read inside the transaction.
- **The two seeded accounts store plain text passwords.** Anything created
  through the manage users screen is hashed; the originals were left working so
  nobody is locked out.
- **Shift handovers assume one closing per day.** Expected cash is that
  cashier's whole day of sales, so closing twice in a day counts the same sales
  twice.
- **Deleting a category deletes its products,** and deleting a product deletes
  its order lines. There is no confirmation step.

---

## Screenshots

1. Login page

    ![Login page](screenshot/1.JPG)

2. Manage products

    ![Manage products](screenshot/2.JPG)

3. Add a new product

    ![Add new product](screenshot/3.JPG)

4. Manage categories

    ![Manage categories](screenshot/4.JPG)

5. Cashier register

    ![Cashier register](screenshot/5.JPG)

---

## Credits

Original project by **Lenard Mangay-ayam** ([lenard123/simple-pos](https://github.com/lenard123/simple-pos)).
Extended for ISB37904 coursework by Adam Haikal, Amirul and Hadri.
