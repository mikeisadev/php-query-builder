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

echo DB::table('table_name')
    ->select()
    ->getQuery();
// QUERY: SELECT * FROM table_name

echo '<br/>';

echo DB::table('table_name')
    ->select('*')
    ->getQuery();
// QUERY: SELECT * FROM table_name

echo '<br/>';

echo DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->getQuery();
// QUERY: SELECT column_1, column_2 FROM table_name

echo '<br/>';

echo DB::table('table_name')
    ->select('only_one_column')
    ->getQuery();
// QUERY: SELECT only_one_column FROM table_name

echo '<br/>';

/**
 * Add aggregate functions (COUNT, MIN, MAX, AVG, SUM).
 * 
 * Using a second array as parameter in select method, you can select
 * a column an use an SQL aggregate function.
 */

echo DB::table('table_name')
    ->select(['column_1'], ['column_1' => 'COUNT'])
    ->getQuery();
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
echo DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', 'text') // Default comparator is "="
    ->getQuery();

echo '<br/>';

echo DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', 'LIKE', 'string') // Default comparator is "="
    ->getQuery();

echo '<br/>';

echo DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', '>', 10) // Default comparator is "="
    ->getQuery();

echo '<br/>';

/**
 * Add multiple AND conditions.
 * 
 * You use a bidimensional array to put multiple AND conditions.
 */
echo DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where([
        ['column_1', '>', 10],
        ['column_2', '<', 50],
    ])
    ->getQuery();

echo '<br/>';

// This is the same thing as above but repeating the where method.
echo DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where('column_1', '>', 10)
    ->where('column_2', '<', 50)
    ->getQuery();

echo '<br/>';

/**
 * OR where condition.
 */
$q = DB::table('table_name')
    ->select(['column_1', 'column_2'])
    ->where([
        ['column_1', '>', 10],
        ['column_2', '<', 50]
    ])
    ->orWhere([
        ['column_1', '>', 100],
        ['column_2', '<', 200]
    ]);

echo $q->getQuery();

echo "<pre>";
print_r($q->wheres);
echo "</pre>";

echo "<pre>";

// TESTING
// print_r(DB::table('wp_posts')
//     ->select(['id', 'post_title'], ['id' => 'COUNT'])
//     ->where([['id', '=', 5]])
//     ->whereNotBetween('id', 1, 5)
//     ->orWhereBetween('id', 5, 10)
//     ->whereNotIn('id', [1, 2, 3, 4, 5])
//     ->groupBy('id')
//     ->having('id', '=', '1')
//     ->addHavingFunction(['id' => 'COUNT'])
//     ->having('post_title', '!=', 'mais')
//     ->addHavingFunction(['post_title' => 'AVG'])
//     ->get());
// echo "</pre>";

// COMPLEX QUERY
// print_r( DB::table('wp_postmeta')
// ->select(['post_id', 'meta_id_count' => 'meta_id'], ['meta_id' => 'COUNT'])
// ->groupBy('post_id')
// ->having('meta_id', '>', '4')
// ->addHavingFunction(['meta_id' => 'COUNT'])->get() );

// SAVE IN VARIABLE AND USE LATER.
$wp_postmeta = DB::table('wp_postmeta')
->select(['post_id', 'meta_id' => 'meta_id_count'], ['meta_id_count' => 'COUNT'])
->groupBy('post_id')
->having([['meta_id', '>', '4'], ['post_id', '>', '17']])
->addHavingFunction(['meta_id' => 'COUNT'])
->orderBy(['post_id', 'meta_id_count'], 'ASC')
->limit(25)
->offset(2);


print_r( $wp_postmeta->get() );
// print_r( $wp_postmeta->count() );

// $wp_posts = QueryBuilder::table('wp_postmeta')
//     ->select(['meta_id', 'post_id'], ['meta_id' => 'COUNT'], ['meta_id' => 'meta_dio', 'post_id' => 'h'])
//     ->groupBy(['post_id'])
//     ->having([['meta_dio', '>', 50], ['meta_dio', '<', 60]])
//     ->get();

// $wp_posts = QueryBuilder::table('wp_postmeta')
//     ->select(['post_id'], [], ['meta_id' => 'meta_dio'], true)
//     ->get();

// echo "<pre>";
// print_r($wp_posts);
// echo "</pre>";