<?php
use App\Init;
use App\DB\DB;
use App\DB\Enums\DBEnum;

require_once 'app\Init.php';

$init = new Init();

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
->select(['post_id', 'meta_id_count' => 'meta_id'], ['meta_id' => 'COUNT'])
->groupBy('post_id')
->having([['meta_id', '>', '4'], ['post_id', '>', '17']])
->addHavingFunction(['meta_id' => 'COUNT'])
->orderBy(['post_id', 'meta_id_count'], 'ASC')
->limit(25)
->offset(2);

print_r( $wp_postmeta->getQuery() );
print_r( $wp_postmeta->count() );

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