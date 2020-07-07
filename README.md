# Freezer
Freezer is a tool to help developers discover which database records are created by some application's action and which tables received these new records because that action.

It makes much easier to spot these tables and its new records, understand how the third-party application interacts with the database and also how relationships are made.

## Latest version

0.16.2

Currently only MySQL/MariaDB is supported.

## Objective

The goal of this tool is to shorten the time that developers need to understand the structure of a given database identifying in which tables the third-party application, like [**Moodle**](https://moodle.org), [**HumHub**](https://www.humhub.com) or [**Elgg**](https://elgg.org) for instance, inserts new records depending on the action the application performed, so the developer can focus directly on the tables that primarily matter for that specific action.

This tool makes it much easier to spot these tables and its new records across all tables and to understand how the third-party application interacts with the database, and also how relationships are made.

Or maybe you just want to quickly know which tables are affected by some application's action.

Freezer can connect to any MySQL/MariaDB database, not just "third-party applications' databases".

## Showcase

In this use case I want to know which records are inserted in Moodle's database when an assignment is created.

I chose Moodle for this demo specifically because that database is quite complex.

![Freezer demo video](https://i.imgur.com/TgJOIfd.gif)

## Installation
1. You must have a web server with PHP. PDO extension must be enabled to access the third-party application's MySQL server.
2. Clone the Freezer repository:

```
$ git clone https://github.com/llagerlof/freezer.git
```

3. Copy the file `config/freezer.example.php` to `config/freezer.yourdatabase.php`
4. Edit `freezer.yourdatabase.php` and configure it accordingly (instructions inside)
5. Go to `http://localhost/freezer` (or whatever is the URL for your local web server)

**IMPORTANT**:
- *Freezer is intended to access only **localhost databases** for research purposes, or at most databases accessed by a single person.*
- *Don't put this tool in production environment as there isn't any authentication method to access it.*
- *This application only performs `SELECT`, `DESC` and `SHOW TABLES` statements on configured databases.*

## How to use
1. Open any application that access a database you have in you computer (e.g. *HumHub*, or your own application) and get to the point where you want to start tracking the inserts (e.g. **BEFORE** clicking on the "SAVE" button of some record).
2. On Freezer, select `yourdatabase` in the combo box and click on the **Freeze** button.
3. Do some insert operations in the third-party application (e.g. clicking on the "SAVE" button).
4. On Freezer, click on the **What is New** button.

Freezer will show you which records were inserted in all database's tables between the **Freeze** and **What is New** commands.
