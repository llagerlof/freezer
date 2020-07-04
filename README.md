# Freezer
Freezer is a tool to help developers track which database records are inserted by other programs.

## Current version
0.16.2

## Objective
When developers need to work with or understand databases of third-party applications (e.g. *Moodle*, *HumHub*, *Elgg*), it is often useful to know what exactly is inserted in the database when a certain action is performed in the third-party application.

This tool makes it much easier to spot these new records across all tables and to understand how the third-party application interacts with the database, and also how relationships are made.

Currently only MySQL is supported.

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
1. Open any third-party application (e.g. *Moodle*) and get to the point where you want to start tracking the inserts (e.g. **before** clicking on the SAVE button of some record).
2. On Freezer, select `yourdatabase` in the combo box and click on the **Freeze** button.

![Freezer launch screen](https://i.imgur.com/9VfVvHe.png)

3. Do some insert operations in the third-party application (e.g. clicking on the SAVE button).
4. On Freezer, click on the **What is New** button.

Freezer will show you which records were inserted in all database's tables between the **Freeze** and **What is New** commands.
