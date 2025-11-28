# **AR SYSTEM – Account Receivable System**

A centralized **multi‑database Account Receivable System** built in Laravel. Each user is assigned a specific business unit database, while administrators have full access to all databases.

---

## 🚀 **System Overview**

The system operates with **one Laravel project** but multiple databases. Behavior varies by role:

### **👨‍💼 Admin Behavior**

* Can access **all databases**.
* Can **switch the active database** after logging in.
* When an admin creates a new user, the **user inherits the admin’s current active database**.

### **👤 Normal User Behavior**

* Has access **only to the database assigned** at account creation.
* Cannot switch databases.

### **📌 How Database Switching Works**

* **Web requests** use the database stored in the **session**.
* **Background processes** (Reverb, Queue workers) always use the **default DB defined in `.env`**, unless explicitly overridden.

---

## 🛠️ **Project Setup (After Cloning)**

Run the following commands:

```bash
git clone <repository-url>
cd <project-folder>
composer install
npm install
```

### 1️⃣ **Create storage link**

> Make sure there is **NO** existing `public/storage` folder.

```bash
php artisan storage:link
```

If the folder exists:

1. Delete `public/storage`
2. Run the command again.

---

## ⚠️ **Common Issues & Fixes**

### ❌ **1. Images Not Showing**

✔ Run:

```bash
php artisan storage:link
```

---

### ❌ **2. Report Progress Stuck at 100%**

This is caused by incorrect queue settings.

✔ Open `config/queue.php` and ensure:

```php
'default' => env('QUEUE_CONNECTION', 'database'),
```

And the **connection** is using **mysql**.

---

### ❌ **3. Report Generation Shows 403 Access Denied**

Example error:

> *ERROR 403 Access Denied – The gates are firmly shut…*

This happens when `storage:link` is not properly created.

✔ Solution:

```bash
php artisan storage:link
```

Make sure it runs **successfully**, and ensure:

* No `public/storage` folder existed before linking.
* Your generated files are saved under:

  ```
  storage/app/public/
  ```

---

## 📌 Additional Notes

* Admins should **switch to the correct Business Unit** before creating users.
* Queue workers and Reverb processes **do not use session-based DB**. They use the DB defined in the `.env` unless dynamically reconfigured.

---

## 📞 **Support**

Message the developer if issues persist or if additional business units need setup.

---

**Enjoy using AR System – centralized, efficient, and scalable.**
