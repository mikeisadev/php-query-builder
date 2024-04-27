<?php
use App\Init;   // Solve Init class namespace.
use App\DB\DB;  // Solve DB class namespace.

require_once 'app\Init.php';    // Require the Init class.

Init::getInstance();    // Init project.

/**
 * Code examples
 * 
 * Select a table and some columns.
 * 
 * Column selection can be done via an array or via a single string for one column.
 */

DB::table('table_name')
    ->select();
// QUERY: SELECT * FROM table_name

echo '<br/>';

DB::table('table_name')
    ->select('*');
// QUERY: SELECT * FROM table_name


echo '<br/>';

DB::table('table_name')
->select(['column_1', 'column_2']);
// QUERY: SELECT column_1, column_2 FROM table_name

echo '<br/>';

DB::table('table_name')
    ->select('only_one_column');
// QUERY: SELECT only_one_column FROM table_name

echo '<br/>';

/**
 * Add aggregate functions (COUNT, MIN, MAX, AVG, SUM).
 * 
 * Using a second array as parameter in select method, you can select
 * a column an use an SQL aggregate function.
 */

DB::table('table_name')
    ->select(['column_1'], ['column_1' => 'COUNT']);
// QUERY: SELECT COUNT(column_1) FROM table_name

echo '<br/>';

/**
 * You can select a column with another name using "AS" statement.
 * 
 * You simply transform the first array into an associative one.
 * 
 * The key will be the column name and the value will be the new name of the column.
 */

DB::table('table_name')
    ->select(['column_1' => 'custom_name'], ['column_1' => 'COUNT']);
// QUERY: SELECT COUNT(column_1) AS custom_name FROM table_name

echo '<br/>';

/**
 * Add conditions.
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', 'text'); // Default comparator is "="
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 = text

echo '<br/>';

DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', 'LIKE', 'string'); // The term between column and value is the comparator
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 LIKE text

echo '<br/>';

DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', '>', 10);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10

echo '<br/>';

/**
 * Add multiple AND conditions.
 * 
 * You use a bidimensional array to put multiple AND conditions.
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where([
        ['column_1', '>', 10],
        ['column_2', '<', 50],
    ]);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10 AND column_2 < 50

echo '<br/>';

// This is the same thing as above but repeating the where method.
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', '>', 10)
    ->where('column_2', '<', 50);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE column_1 > 10 AND column_2 < 50

echo '<br/>';

/**
 * OR where condition.
 */
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

echo '<br/>';

/**
 * WHERE NOT
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->whereNot('column_1', '>', 10);
// QUERY: SELECT column_1, column_2 FROM table_name WHERE NOT column_1 > 10

echo '<br/>';

/**
 * ORDER BY.
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->orderBy('column_1', 'ASC');
// QUERY: SELECT column_1, column_2 FROM table_name ORDER BY column_1 ASC

echo '<br/>';

/**
 * LIMIT.
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->limit(25);
// QUERY: SELECT column_1, column_2 FROM table_name LIMIT 25

echo '<br/>';

/**
 * LIMIT with starting offset.
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->limit(5, 25); // Start from fifth result up to 25 position.
// QUERY: SELECT column_1, column_2 FROM table_name LIMIT 5, 25

echo '<br/>';

/**
 * OFFSET.
 */
DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->offset(5); // Start from fifth result.
// QUERY: SELECT column_1, column_2 FROM table_name OFFSET 5

echo '<br/>';

/**
 * GROUP BY
 */
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1');
// QUERY: SELECT column_1 FROM table_name GROUP BY column_1

echo '<br/>';

/**
 * HAVING
 */
DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50);
// QUERY: SELECT column_1 FROM table_name GROUP BY column_1 HAVING column_1 > 50

echo '<br/>';

/**
 * Get all results
 * 
 * Use ->get() at the end.
 */
// DB::table('table_name')
//     ->select('column_1')
//     ->groupBy('column_1')
//     ->having('column_1', '>', 50)
//     ->get();

echo '<br/>';

/**
 * Get first result only.
 */
// DB::table('table_name')
//     ->select('column_1')
//     ->groupBy('column_1')
//     ->having('column_1', '>', 50)
//     ->first();

echo '<br/>';

/**
 * Count the results.
 */
// DB::table('table_name')
//     ->select('column_1')
//     ->groupBy('column_1')
//     ->having('column_1', '>', 50)
//     ->count();

echo '<br/>';

/**
 * Debugging.
 * 
 * For debugging you can use getQuery() method at the end
 * to get the built query.
 */
echo DB::table('table_name')
    ->select('column_1')
    ->groupBy('column_1')
    ->having('column_1', '>', 50)
    ->getQuery();

echo '<br/>';

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

echo '<br/>';

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

echo '<br/>';

/**
 * To prevent SQL injection use setParams.
 * 
 * In your clause where you accept external user input use :unique_string.
 * 
 * In the example below I set ':col' in the where clause to get an external user input to manipulate the query.
 */
// $col1 = 400;
// DB::table('table_name')
//     ->select('column_1')
//     ->where('column_1', '>', ':col')
//     ->setParams([
//         ':col' => $col1
//     ]);

// echo '<br/>';

// SQL Injection is blocked.
// $col2 = 400 . 'OR 1=1';
// DB::table('table_name')
//     ->select('column_1')
//     ->where('column_1', '>', ':col')
//     ->setParams([
//         ':col' => $col2
//     ])
//     ->get();

// echo '<br/>';

// This is vulnerable to SQL injection (not using setParams function).
// $col3 = 400 . 'OR 1=1';
// DB::table('table_name')
//     ->select('column_1')
//     ->where('column_1', '>', $col3)
//     ->get();