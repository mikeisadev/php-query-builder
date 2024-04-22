# PHP Query Builder

This a simple OOP PHP query builder based on PDO (PHP Data Object).

The query builder use prepared statements and value binding to prevent SQL injection, making your queries secure to perform on your database.

**Note**: this utility class is already structured in a certain folder structure. The PHP Query Builder lives inside `./app/database` directory. If you don't like this folder structure, you can take all the files inside `./app/database` and move inside the `./src` or `./inc` directory of your project. Then, you'll have to require all the classes and set up the .env file with database credentials.

## Requirements

- PHP 8.2 or greater.

## Project info and status

- Released on 22 April 2024
- Based on OOP PHP and PDO

- Required composer packages
    - vlucas/dotenv

- Supported databases:
    - MySQL / MariaDB
    - PostgreSQL
    - SQLite

- Queries you can do:
    - SELECT statements

- Queries you CANNOT do:
    - DELETE
    - UPDATE
    - INSERT

The queries that actually you cannot do will be available soon.

## Before to start

Before to start install required composer packages running:

```
composer install
```

## Setup

Before to start with the project setup, I want to talk to you about the folder structure of the project.

**Note**: as I said at the beginning, this project already has a folder structure. If you only want the query builder you can copy all the files and folders inside `./app/database` inside your project. But then you'll need to require all classes in your project (in this one **Init.php** does all this stuff) and a way to manage the `.env` file to store database credentials.

The PHP Query Builder is inside `./app/database` folder. In this folder we have these files and in this list I explain what they do in short:

- `DB.php` handles the connection to the database through PDO getting database connection credentials from the .env file loaded by the vlucas/dotenv dependecy.
- `QueryBuilder.php` is the actual class to build a query.
- Inside `.app/database/enums/DBEnum.php` there is an enumeration for SQL statements types (this file is necessary but it's not necessary that you understand what each string does).

"**DB**" class (inside the `DB.php` file) is the starting class from which you'll build your queries. 

When calling a static method from "**DB**" (for example `DB::table()`) you'll then instantiate a "**QueryBuilder**" (from `QueryBuilder.php` file) class to start chaining methods.

The `Init.php` (inside `./app` folder) file is used to require all classes and `autoload.php` from `./vendor` folder to get all the dependencies.

Inside `index.php` I required the **Init.php** class using its namespace.

Now I can start using the **QueryBuilder**, so we'll go on with some examples: