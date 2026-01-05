# **AR SYSTEM – Account Receivable System**

A centralized **multi‑database Account Receivable System** built in Laravel. Each user is assigned a specific business unit database, while administrators have full access to all databases.

---

## 🚀 **System Overview**

The system operates with **one Laravel project** but multiple databases. Behavior varies by role:

### **👨‍💼 Admin Behavior**

* Can access **all databases**.
* Can **switch the active database** after logging in.
* When an admin creates a new user, the **user will be set what database it can be use base on admin selected**.

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

### ❌ **4. Report Generation Failed to preview**

Example error:

> *ERROR 403 Access Denied – Failed to preview report*

This happens when `nssm reverb and queue` is not properly created.

✔ Solution:

Check again the nssm reverb the host and port must match on the server ip and the host must match on what is set in the reverb found in the .env file.
Then restart the nssm reverb and nssm queue that you setup.

---

### ❌ **5. The Pdf job report doesnt show error, and stuck in 98% or 100%**

✔ Solution: The problem is the job executed uses the default .env setup. It doesnt use the database set in the session. so that why the data dont match because the request use the session data and the job compare it to the .env database set.
So the solution is you must pass the session active database and use it in the controller and pass to job file to override the job to use the session database setup.

You must pass the
---

## This setup is for giving access to database from centralized project using UniServerZ
# Please note that the commands there is just a sample just change the thing base on the user and the database name you created. All commands there is just a guide so please be watchful.

*First is that you must give access the first database or the main database that is setup on the project .env file

*The database is automatically created so youll need to do is to create access to specific user
Run this command for giving access to specific user
```bash
CREATE USER 'SampleUser'@'172.16.42.91' IDENTIFIED BY 'FarMsTeaM';
```
*That command create a user that is being recieve the access and its corresponding ip address and the password of the database to access
*Next is grant that user the access of the database
```bash
GRANT ALL PRIVILEGES ON sample_database.* TO 'SampleUser'@'172.16.42.91'; 
```
then run 
```bash
FLUSH PRIVILEGES;
```
finally run this command
```bash
GRANT ALL PRIVILEGES ON `sample_database`.* TO 'admin'@'localhost';
FLUSH PRIVILEGES;
```

Thats all for the first and main database setup in the project .env file

*Next is to add more database on the same user
*First is creating new database, run this command
```bash
CREATE DATABASE second_sample_database;
```
then run this command
```bash
GRANT ALL PRIVILEGES ON second_sample_database.* TO 'SampleUser'@'172.16.42.91'; 
FLUSH PRIVILEGES;
```
finally run this command
```bash
GRANT ALL PRIVILEGES ON `second_sample_database`.* TO 'admin'@'localhost';
FLUSH PRIVILEGES;
```

## 📌 Additional Notes

* Admins can now set the newly added users on database which he or she can use only.
* Queue workers and Reverb processes **do not use session-based DB**. They use the DB defined in the `.env` unless dynamically reconfigured.

---

## 📞 **Support**

Message the developer if issues persist or if additional business units need setup.

---

**Thank you and Godbless.**
