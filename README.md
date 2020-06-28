# Freezer
Freezer is a tool to help developers to track which database inserts are made by other programs.

## Current version
0.10.2

## Objective
When developers need to work or understand databases of third-party applications (for example, *Moodle*, *HumHub*, *Elgg*, or any program that performs insert operations in the database) it is often useful to know exactly what is inserted when a certain operation in the third-party application is performed.

This tool makes much easier to spot this new records across the tables and to understand how the third-party application interacts with the database, and how relationships are made.

## Installation
1. You must have a web server with PHP and PDO extension enabled. And MySQL.
2. Clone the Freezer repository:

```
$ git clone https://github.com/llagerlof/freezer.git
```

3. Copy the file `config/freezer.example.php` to `config/freezer.yourdatabase.php`
4. Configure the `freezer.yourdatabase.php` (instructions inside)
5. Go to `http://localhost/freezer` (or whatever URL is for your local web server)

**IMPORTANT**:
- *Freezer is intended to use only on localhost connections. At current state it should not run on a multi-user environment or production. There isn't any security implemented.*
- *This program only runs SELECT statements on configured databases.*

## How to use
1. Open some third-party application (eg. *Moodle*) and go to the point where you want to start tracking the inserts (eg. **before** clicking on the SAVE button of some record).
2. On Freezer, select `yourdatabase` in the combo box and click the **Freeze** button.

![Freezer launch screen](https://i.imgur.com/9VfVvHe.png)

3. Do some insert operations in the third-party application (eg. clicking in the SAVE button).
4. On Freezer, click the **What is New** button.

Freezer will show you which records were inserted in all database's tables between the **Freeze** and **What is New** commands.
