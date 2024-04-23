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

## Setup

### Before to start the setup

Before to start with the project setup, I want to talk to you about the folder structure of the project.

**Note**: as I said at the beginning, this project already has a folder structure. If you only want the query builder you can copy all the files and folders inside `./app/database` inside your project. But then you'll need to require all classes in your project (in this one **Init.php** does all this stuff) and a way to manage the `.env` file to store database credentials.

The PHP Query Builder is inside `./app/database` folder. In this folder we have these files and in this list I explain what they do in short:

- `DB.php` handles the connection to the database through PDO getting database connection credentials from the .env file loaded by the vlucas/dotenv dependecy.
- `QueryBuilder.php` is the actual class to build a query.
- Inside `.app/database/enums/DBEnum.php` there is an enumeration for SQL statements types (this file is necessary but it's not necessary that you understand what each string does).

"**DB**" class (inside the `DB.php` file) is the starting class from which you'll build your queries. 

When calling a static method from "**DB**" (for example `DB::table()`) you'll then instantiate a "**QueryBuilder**" (from `QueryBuilder.php` file) class to start chaining methods.

The `Init.php` (inside `./app` folder) file is used to require all classes and `autoload.php` from `./vendor` folder to get all the dependencies.

Inside the `Init.php` file I load an important package to load database credentials from `.env` file called *vlucas/dotenv* requiring the `autoload.php` file.

Inside `index.php` I required the **Init.php** class using its namespace to load everything.

### Setup the query builder

First, install the required packages:

```
composer install
```

The second step is to setup the `.env` file. This project already comes with an **.env.example** file, you must rename this file into **.env**.

There you'll insert all the credentials to connect to your database.

Inside the **index.php** I required the **Init.php** file (inside the `./app` folder) to load everything.

## Code examples

**NOTE**: Inside the index.php file there are all the examples.

Select all query examples:

```php
DB::table('table_name')
    ->select();

DB::table('table_name')
    ->select('*');

// QUERY: SELECT * FROM table_name
```

Select one or more columns.

You can use an array to select all the columns that you need. 

Or you can use a string to select only one column.

```php
DB::table('table_name')
    ->select(['column_1', 'column_2']);
// QUERY: SELECT column_1, column_2 FROM table_name

DB::table('table_name')
    ->select('only_one_column');
// QUERY: SELECT only_one_column FROM table_name
```

Using aggregate functions

Use a second array after the columns that you want to select.

The second array will be an associative one where you put the column as the key and the value as the function.

```php
DB::table('table_name')
    ->select(['column_1'], ['column_1' => 'COUNT']);
// QUERY: SELECT COUNT(column_1) FROM table_name
```

Select a column as another name.

Inside the array where you select all the columns (the first one), put the real column name as the key, then use the custom name you want for that column as the value of the associative array.

In addition I added the COUNT function to the column.

```php
DB::table('table_name')
    ->select(
        ['column_1' => 'custom_name'], 
        ['column_1' => 'COUNT']
    );
// QUERY: SELECT COUNT(column_1) AS custom_name FROM 
```

Conditional SQL statements and WHERE clauses.

You can use the where method and compare one column to a value.

If you don't put any operator at the center of the where method

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', 'text'); // Default comparator is "="
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 = text 
```

You can put any operator between a column and a value

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', 'LIKE', 'string'); // The term between column and value is the comparator
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 LIKE text

DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', '>', 10);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10
```

Using AND with multiple conditions.

Inside the where method you use a bidimensional array.

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where([
        ['column_1', '>', 10],
        ['column_2', '<', 50],
    ]);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10 AND column_2 < 50
```

You can also do AND with multiple conditions this way:

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', '>', 10)
    ->where('column_2', '<', 50);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10 AND column_2 < 50
```

**OR** condition with `orWhere` method

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where([
        ['column_1', '>', 10],
        ['column_2', '<', 50]
    ])
    ->orWhere([
        ['column_1', '>', 100],
        ['column_2', '<', 200]
    ]);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10 AND column_2 < 50 OR column_1 > 100 AND column_2 < 200
```

**WHERE NOT** condition with `whereNot` method

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->whereNot('column_1', '>', 10);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE NOT column_1 > 10
```

**ORDER BY** with `orderBy` method

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->orderBy('column_1', 'ASC');
// QUERY: SELECT column_1, column_2 FROM table_name ORDER BY column_1 ASC
```

**LIMIT** with `limit` method

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->limit(25);
// QUERY: SELECT column_1, column_2 FROM table_name LIMIT 25
```

You can use limit with a starting offset using two parameters

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->limit(5, 25); // Start from fifth result up to 25 position.
// QUERY: SELECT column_1, column_2 FROM table_name LIMIT 5, 25
```

To set an offset you can also use the `offset` method

```php
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->offset(5); // Start from fifth result.
// QUERY: SELECT column_1, column_2 FROM table_name OFFSET 5
```

**GROUP BY** statement

```php
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1');
// QUERY: SELECT column_1 FROM table_name GROUP BY column_1
```

**HAVING** statement along with the **GROUP BY** statement

```php
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50);
// QUERY: SELECT column_1 FROM table_name GROUP BY column_1 HAVING column_1 > 50
```

## Getting the results

Chain the get method at the end of each valid built queries to get all the results:

```php
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50)
    ->get();
```

Or you can get only the first result:

```php
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50)
    ->first();
```

You can also count the results:

```php
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50)
    ->count();
```

## Prevent SQL injection

Preventing SQL injection is crucial to make your web app safe. 

You can use the `setParams` method that accepts an array as argument.

You set the key as the placeholder to substitute, as value the dynamic input to sanitize.

```php
$col1 = 400;
DB::table('table_name')
    ->select('column_1')
    ->where('column_1', '>', ':col')
    ->setParams([
        ':col' => $col1
    ]);

// Here SQL Injection is also blocked.
$col2 = 400 . 'OR 1=1';
DB::table('table_name')
    ->select('column_1')
    ->where('column_1', '>', ':col')
    ->setParams([
        ':col' => $col2
    ])
    ->get();

// This is vulnerable to SQL injection (not using setParams function).
$col3 = 400 . 'OR 1=1';
DB::table('table_name')
    ->select('column_1')
    ->where('column_1', '>', $col3)
    ->get();
```

## Debugging

To debug your built query you can use the `getQuery` method to get the full query.

Or you can use the `getParams` method to get the inserted params to prevent **SQL Injection**.

Instead, you can get both with `getQueryAndParams` method.

```php
/**
 * Get the full query.
 */
echo DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50)
    ->getQuery();

/**
 * Get params.
 */
$col1 = 400;
print_r( DB::table('table_name')
    ->select('column_1')
    ->where('column_1', '>', ':col')
    ->setParams([
        ':col' => $col1
    ])
    ->getParams() );

/**
 * Get query and params
 */
$col1 = 400;
print_r( DB::table('table_name')
    ->select('column_1')
    ->where('column_1', '>', ':col')
    ->setParams([
        ':col' => $col1
    ])
    ->getQueryAndParams() );
```