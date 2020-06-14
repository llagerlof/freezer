# Freezer
Freezer is a tool to help developers to track which database inserts are made by other programs.

## Objective
When the prodeveloper needs to work with databases of third-party applications (for example, Moodle, HumHub, Elgg, or any program that performs insert operations in the database), it is often useful to know exactly what is inserted in the database when a certain operation in the third-party application is performed.

This makes it much easier to understand how the third-party application interacts with the database and how relationships are made.

## Installation
1. You must have a PHP web server (with PDO) installed and MySQL.
2. Download the [Freezer repository](https://github.com/llagerlof/freezer).
3. Copy the file `config/freezer.example.php` to `config/freezer.yourdatabase.php`
4. Configure the `freezer.yourdatabase.php` (instructions inside)
5. Go to `http://localhost/freezer` (or whatever URL is for your local web server)

*IMPORTANT: Freezer is intended to use only on localhost connections. At current state it should not run on a multi-user environment or production. There isn't any security implemented.*

## How to use
1. Open the third-party application and go to the point where you want to start tracking (eg: before clicking on the SAVE button of a record).
2. On Freezer, click the **Freeze** button.

![Freezer launch screen](https://i.imgur.com/9VfVvHe.png)

3. Do some insert operations in the third-party application (eg: clicking in the SAVE button).
4. On Freezer, click the **What is New** button.

Freezer will show you which records were inserted in all database's tables between the **Freeze** and **What is New** commands.
