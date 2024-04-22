# PHP Query Builder

This a simple OOP PHP query builder based on PDO (PHP Data Object).

The query builder use prepared statements and value binding to prevent SQL injection, making your queries secure to perform on your database.

## Requirements

- PHP 8.2 or greater.

## Project info and status

- Released on 22 April 2024
- Based on OOP PHP and PDO

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

## Setup

The PHP Query Builder is inside ./app/database, so all the 