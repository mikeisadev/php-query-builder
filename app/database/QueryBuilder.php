<?php

namespace App\DB;

use App\DB\Enums\DBEnum;

class QueryBuilder {

    /**
     * Current database connection.
     * 
     * Loaded on construct (instance).
     */
    private ?\PDO $connection = null;

    /**
     * Current database tables.
     * 
     * Loaded on construct (instance).
     */
    private ?array $tables = null;

    /**
     * The final built query.
     */
    private string $query = '';

    /**
     * Select statement.
     */
    public array $select = [];

    /**
     * The database table to select from.
     */
    private string $from = '';

    /**
     * where statements.
     */
    public array $wheres = []; 

    /**
     * Group by statements.
     */
    private array $groupby = [];

    /**
     * Having statements.
     */
    private array $havings = [];

    /**
     * Order by parameters.
     */
    private array $orderBy = [];

    /**
     * Limit parameter.
     */
    private array $limit = [];

    /**
     * Offset parameter.
     */
    private ?int $offset = null;

    /**
     * Count the conditions added.
     */
    private int $conditionCount = 0;

    /**
     * Last having statements in $havings array.
     */
    private ?int $havingCount = null;

    /**
     * Key value pairs of the user inputs to build the query.
     * 
     * Used in PDO bindValue function.
     *
     * :key => value
     */
    private array $params = [];

    /**
     * SQL functions.
     */
    private array $functions = ['COUNT', 'MIN', 'MAX', 'AVG', 'SUM'];

    /**
     * List SQL statements that can have functions.
     */
    private array $canHaveFunctions = [
        'SELECT' => true,
        'WHERE' => false,
        'GROUP_BY' => false,
        'HAVING' => true
    ];

    /**
     * SQL Comparison signs.
     */
    private array $compOperators = ['=', '>', '<', '>=', '<=', '<>', '!=', 'NOT', 'LIKE'];

    /**
     * Common SQL syntax.
     */
    private array $syntax = ['*'];

    /**
     * Order types.
     */
    private array $ordering = ['ASC', 'DESC'];

    /**
     * Bool to check if using LIMIT offset.
     */
    private bool $usingLimitOffset = false;

    /**
     * Constructor.
     * 
     * Set database connection via PDO.
     * Set a whitelist of database tables.
     */
    public function __construct(\PDO $connection, array $tables) {
        // Set db connection.
        $this->connection = $connection;

        // Insert current db tables.
        $this->tables = $tables;
    }

    /**
     * Select the table + connect to the database.
     */
    public function table(string $table): QueryBuilder {
        if ( !is_string($table) ) throw new \Exception('The parameter $table must be a string!');

        if ( 'yes' === strtolower($_ENV['DB_CHECK_TABLES']) && !in_array( $table, $this->tables ) ) throw new \Exception('The table does not exist in the database!');

        $this->from = $table;

        return $this;
    }

    /**
     * Select one or more columns statement.
     * 
     * To use functions in select statements.
     * $functions = [
     *  'id' => 'COUNT'
     * ]
     */
    public function select(string|array $column = '*', array $functions = [], bool $distinct = false): QueryBuilder {
        // Default select all or if is string.
        if ( '*' === $column || is_string($column) ) $this->select[] = $column;

        // If we have an array.
        if ( is_array($column) ) {
            foreach ($column as $as => $col) {
                if ( '__FUNCTIONS__' === $as ) throw new \Exception('You cannot call a column as "__FUNCTIONS__"');
                if ( is_string($as) ) {
                    $this->select[$as] = $col;
                } else {
                    $this->select[] = $col;
                }
            }
        }

        // Build functions.
        if ( $functions ) {
            foreach ($functions as $col => $func) {
                if ( !array_key_exists($col, $this->select) && !in_array($col, $this->select) ) {
                    throw new \Exception('The column ' . $col . ' is not present between SELECT columns!');
                }

                if ( !in_array($func, $this->functions) ) {
                    throw new \Exception('The function ' . $func . ' is not valid!');
                }

                $this->select['__FUNCTIONS__'] = [ $col => $func ];
            }
        }

        // Is "SELECT DISTINCT" statement?
        if ( $distinct ) {
            $this->select['__IS_DISTINCT__'] = true;
        }

        return $this;
    }

    /**
     * Select distinct.
     */
    public function selectDistinct(string|array $column = '*', array $functions = []) {
        return $this->select($column, $functions, true);
    }

    /**
     * Build a "WHERE" statement.
     * 
     * The function can be constructed in these ways.
     * 
     * With strings.
     * where('column', 'compare', 'value');
     * 
     * With arrays.
     * where([
     *  ['column1', 'compare', 'value'],
     *  ['column2', 'compare', 'value'],
     *  ['column3', 'compare', 'value']
     * ]);
     */
    public function where(
        string|array $column, 
        ?string $compare = null, 
        ?string $value = null, 
        string $bool = 'AND',
        ?string $operator = null,
        ?DBEnum $type = null
    ): QueryBuilder {
        $this->addConditions(
            'wheres',
            $column,
            $compare,
            $value,
            $bool,
            $operator,
            is_null($type) ? DBEnum::AND_WHERE : $type
        );
        
        return $this;
    }

    /**
     * Buil an "OR WHERE" statement.
     */
    public function orWhere(string|array $column, ?string $compare = null, ?string $value = null): QueryBuilder {
        return $this->where($column, $compare, $value, 'OR', '', DBEnum::OR_WHERE);
    }

    /**
     * Build a "WHERE NOT" statement.
     */
    public function whereNot(string|array $column, ?string $compare = null, ?string $value = null): QueryBuilder {
        return $this->where($column, $compare, $value, 'AND', 'NOT', DBEnum::AND_WHERE_NOT);
    }

    /**
     * Build a "OR WHERE NOT" statement.
     */
    public function orWhereNot(string|array $column, ?string $compare = null, ?string $value = null): QueryBuilder {
        return $this->where($column, $compare, $value, 'OR', 'NOT', DBEnum::OR_WHERE_NOT);
    }

    /**
     * WHERE BETWEEN statement.
     */
    public function whereBetween(string $column, int $from, int $to): QueryBuilder {
        $compare = '';
        $value = $from . '|' . $to;

        return $this->where($column, $compare, $value, 'AND', '', DBEnum::AND_WHERE_BETWEEN);
    }

    /**
     * OR WHERE BETWEEN statement.
     */
    public function orWhereBetween(string $column, int $from, int $to): QueryBuilder {
        $compare = '';
        $value = $from . '|' . $to;

        return $this->where($column, $compare, $value, 'OR', '', DBEnum::OR_WHERE_BETWEEN);
    }

    /**
     * WHERE NOT BETWEEN statement.
     */
    public function whereNotBetween(string $column, int $from, int $to): QueryBuilder {
        $compare = '';
        $value = $from . '|' . $to;

        return $this->where($column, $compare, $value, 'AND', 'NOT', DBEnum::AND_WHERE_NOT_BETWEEN);
    }

    /**
     * WHERE NOT BETWEEN statement.
     */
    public function orWhereNotBetween(string $column, int $from, int $to): QueryBuilder {
        $compare = '';
        $value = $from . '|' . $to;

        return $this->where($column, $compare, $value, 'OR', 'NOT', DBEnum::OR_WHERE_NOT_BETWEEN);
    }

    /**
     * WHERE IN statement.
     */
    public function whereIn(string $column, array $options): QueryBuilder {
        $compare = '';
        $value = '(' . implode(', ', $options) . ')';

        return $this->where($column, $compare, $value, 'AND', '', DBEnum::AND_WHERE_IN);
    }

    /**
     * WHERE NOT IN statement.
     */
    public function whereNotIn(string $column, array $options): QueryBuilder {
        $compare = '';
        $value = '(' . implode(', ', $options) . ')';

        return $this->where($column, $compare, $value, 'AND', 'NOT', DBEnum::AND_WHERE_NOT_IN);
    }

    /**
     * OR WHERE IN statement.
     */
    public function orWhereIn(string $column, array $options): QueryBuilder {
        $compare = '';
        $value = '(' . implode(', ', $options) . ')';

        return $this->where($column, $compare, $value, 'OR', '', DBEnum::OR_WHERE_IN);
    }

    /**
     * OR WHERE NOT IN statement.
     */
    public function orWhereNotIn(string $column, array $options): QueryBuilder {
        $compare = '';
        $value = '(' . implode(', ', $options) . ')';

        return $this->where($column, $compare, $value, 'OR', '', DBEnum::OR_WHERE_NOT_IN);
    }

    /**
     * "Group by" function.
     * 
     * To generate a "GROUP BY" SQL sratement.
     */
    public function groupBy( string|array $column ): QueryBuilder {
        if (is_string($column)) $this->groupby[] = $column;

        if (is_array($column)) {
            foreach ($column as $as => $col) {
                if ( '__FUNCTIONS__' === $col ) throw new \Exception();
                if (is_string($as)) {
                    $this->groupby[$as] = $col;
                } else {
                    $this->groupby[] = $col;
                }
            }
        }

        return $this;
    }

    /**
     * "HAVING" function.
     * 
     * To generate an "HAVING" SQL statement.
     * 
     *  USE AFTER groupBy (GROUP BY) function.
     */
    public function having(
        string|array $column, 
        ?string $compare = null, 
        ?string $value = null,
        string $bool = 'AND',
        ?string $operator = null,
        ?DBEnum $type = null,
    ): QueryBuilder {
        $this->addConditions(
            'havings',
            $column,
            $compare,
            $value,
            $bool,
            $operator,
            is_null($type) ? DBEnum::AND_HAVING : $type
        );

        return $this;
    }

    /**
     * OR HAVING function.
     * 
     * Use after GROUP BY function.
     */
    public function orHaving(string|array $column, ?string $compare = null, ?string $value = null): QueryBuilder {
        return $this->having($column, $compare, $value, 'OR', null, DBEnum::OR_HAVING);
    }

    /**
     * HAVING NOT function.
     */
    public function notHaving(string|array $column, ?string $compare = null, ?string $value = null): QueryBuilder {
        return $this->having($column, $compare, $value, 'AND', 'NOT', DBEnum::AND_NOT_HAVING);
    }

    /**
     * OR HAVING NOT function.
     */
    public function orNotHaving(string|array $column, ?string $compare = null, ?string $value = null): QueryBuilder {
        return $this->having($column, $compare, $value, 'OR', 'NOT', DBEnum::OR_NOT_HAVING);
    }

    /**
     * Add functions like COUNT, MAX, MIN, AVG to the last HAVING statement.
     */
    public function addHavingFunction(array $functions): QueryBuilder {
        $havings = $this->havings[$this->havingCount-1]['conditions'];
        $funcCols = array_keys($functions);
        $columns = (array) [];

        foreach ($havings as $cond) {
            $columns[] = $cond['column'];
        }

        /**
         * Check if the column exists inside the last HAVING statement.
         */
        foreach ($funcCols as $col) {
            if ( !in_array($col, $columns) ) throw new \Exception('The column ' . $col . ' does not exist inside the HAVING statement.');
        }

        /**
         * Add the function(s).
         */
        $this->havings[$this->havingCount-1]['__FUNCTIONS__'] = $functions;

        return $this;
    }   

    /**
     * Order by statement.
     * 
     * The ordering by default is on ASC (ascendant).
     */
    public function orderBy(string|array $column, string $ordering = 'ASC'): QueryBuilder {
        if ( !empty($ordering) && !in_array($ordering, $this->ordering) ) throw new \Exception('The order type is not valid. It can be one of these values: ' . implode(', ', $this->ordering));

        if ( is_string($column) ) $this->orderBy[] = $column;

        if ( is_array($column) ) {
            foreach ($column as $key => $col) {
                if ( is_string($key) ) {
                    if ( !in_array($col, $this->ordering) ) {
                        throw new \Exception('The ordering type inside the associative array ('.$key.' => '.$col.') is not valid.');
                    }

                    $this->orderBy[$key] = $col;
                } else {
                    $this->orderBy[] = $col;
                }
            }
        }

        $this->orderBy['__ORDERING__'] = $ordering;

        return $this;
    }

    /**
     * Limit.
     *
     * Set a limit for the query and a start (offset)
     */
    public function limit(): QueryBuilder {
        $limit = func_get_args();

        if ( count($limit) === 0 ) throw new \Exception("You must insert at least one parameter");
        if ( count($limit) > 2 ) throw new \Exception("You cannot insert more than two parameters");
        if ( !is_numeric($limit[0]) || (array_key_exists(1, $limit) && !is_numeric($limit[1])) ) throw new \Exception("The first or second parameter must be an integer");

        if ( count($limit) === 2 ) $this->usingLimitOffset = true;

        $this->limit = $limit;

        return $this;
    }

    /**
     * Set the offset.
     */
    public function offset(int $offset): QueryBuilder {
        if ( $this->usingLimitOffset ) throw new \Exception('You are already using an offset value inside a "limit" statement!');

        $this->offset = $offset;

        return $this;
    }

    /**
     * Set params of the query.
     *
     * This parameter must be a bi-dimensional array.
     */
    public function setParams(array $params): QueryBuilder {
        $this->params = $params;

        return $this;
    }

    /**
     * Get the results of the query from the database.
     */
    public function get(): array {
        $this->buildQuery();

        return $this->executeQuery('all');
    }

    /**
     * Get the first result only!
     */
    public function first(): array {
        $this->buildQuery();

        return $this->executeQuery('first');
    }

    /**
     * Count the number of rows got from the query.
     */
    public function count(): int {
        $this->buildQuery();

        return $this->executeQuery('count');
    }

    /**
     * Get the built query.
     */
    public function getQuery(): string|array {
        $this->buildQuery();

        return $this->query;
    }

    /**
     * Get all params.
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * Get query and params.
     */
    public function getQueryAndParams(): array {
        $this->buildQuery();

        return [
            'query' => $this->query,
            'params' => $this->params
        ];
    }

    /**
     * Add conditions WHERE or HAVING.
     */
    private function addConditions(
        string $conditionProp,
        string|array $column, 
        ?string $compare = null, 
        ?string $value = null, 
        string $bool = 'AND',
        ?string $operator = null,
        ?DBEnum $type = null 
    ) {
        // Build conditions.
        $conditions = (array) [];

        if (is_string($column)) {
            $conditions[] = [ $column, $compare ];
            $value ? $conditions[0][2] = $value : '';
        } else if (is_array($column)) {
            $conditions = $column;
        }

        $this->{$conditionProp}[$this->conditionCount] = [
            'type'          => $type->value,
            'conditions'    => []
        ];

        foreach ($conditions as $condition) {
            $this->{$conditionProp}[$this->conditionCount]['conditions'][] = [
                'operator'      => $operator ? $operator : '',
                'column'        => $condition[0],
                'compare'       => in_array($condition[1], $this->compOperators) ? $condition[1] : '=',
                'value'         => !array_key_exists(2, $condition) ? $condition[1] : $condition[2],
                'bool'          => $bool
            ];
        }

        $this->conditionCount++;

        // For "HAVINGS" add 1 to the counter.
        if ('havings' === $conditionProp) $this->havingCount++;
    }

    /**
     * Build the entire query.
     */
    private function buildQuery() {
        $query = (string) '';

        // Build SELECT statement.
        if ( $this->select ) {
            // Init select.
            $query .= 'SELECT ';

            $query .= array_key_exists('__IS_DISTINCT__', $this->select) ? 'DISTINCT ' : '';

            // Build the columns to select.
            $query .= $this->select ? $this->buildFunctionStmts( $this->select ) . ' ' : '';

            // Set the table.
            $query .= 'FROM ' . $this->from . ' ';

            // Build all wheres statements.
            $query .= $this->wheres ? $this->buildConditions('wheres', 'WHERE') : '';

            // Build group by statements.
            $query .= $this->groupby ? 'GROUP BY ' . $this->buildFunctionStmts( $this->groupby ) . ' ' : '';

            // Build HAVING statements if we have GROUP BY statement before.
            $query .= $this->groupby && $this->havings ? $this->buildConditions('havings', 'HAVING') : '';

            // Add ORDER BY.
            $query .= $this->orderBy ? 'ORDER BY ' . $this->buildOrderingStmt($this->orderBy) : '';

            // ADD LIMIT.
            $query .= $this->limit ? 'LIMIT ' . ( count($this->limit) === 2 ? "{$this->limit[0]}, {$this->limit[1]} " : $this->limit[0] ) . ' ' : '';

            // ADD OFFSET.
            $query .= $this->offset ? 'OFFSET ' . $this->offset : '';
        }

        // TODO:
        // Build INSERT statement.

        // TODO:
        // Build UPDATE statement.

        // TODO:
        // Build DELETE statement.

        $this->query = $query;
    }

    /**
     * Build SELECT, GROUP BY pieces.
     */
    private function buildFunctionStmts(array $functionalArray): string {
        $select = (string) '';
        $functions = array_key_exists('__FUNCTIONS__', $functionalArray) ? $functionalArray['__FUNCTIONS__'] : false;

        // Remove undesired key/value pairs.
        unset($functionalArray['__FUNCTIONS__']);
        unset($functionalArray['__IS_DISTINCT__']);

        $c = 1;
        $wrap = false;
        $selector = 'col';
        foreach ($functionalArray as $as => $col) {
            // There is a function? If so wrap!
            if ($functions) {
                if (isset($functions[$col])) {
                    $wrap = true;
                    $selector = 'col';
                }

                if (isset($functions[$as])) {
                    $wrap = true;
                    $selector = 'as';
                }
            }

            // Add column...
            $select .= $wrap ? $functions[$$selector].'(' : '';   // Pre function.
            $select .= is_string($as) ? $as : $col;         // Column.
            $select .= $wrap ? ')' : '';                    // After function.

            // As?
            $select .= is_string($as) ? ' AS ' . $col : '';

            // Add comma.
            $select .= $c !== count($functionalArray) ? ', ' : '';

            $wrap = false;
            $c++;
        }

        return $select;
    }

    /**
     * Build "WHERE/WHERE NOT" statement.
     */
    private function buildConditions(string $conditionProp, string $type): string {
        $stmt = $type . ' ';
        $previous = null;

        foreach ($this->{$conditionProp} as $blockCount => $block) {
            $type = $block['type'];
            $conditions = $block['conditions'];
            $previous = $type;
            $isOR = false;

            // In "HAVING" we can use SQL functions, but check if we have functions.
            $functions = str_contains($type, 'HAVING') && array_key_exists('__FUNCTIONS__', $block) ? $block['__FUNCTIONS__'] : false;

            print_r($functions);

            // Loop each condition.
            foreach ($conditions as $count => $condition) {
                $isOR = str_contains($type, 'OR_') ? true : false;

                $bool = $isOR && $count > 0 ? 'AND' : $condition['bool']; // Build the first bool.

                $stmt .= $blockCount > 0 || $count > 0 ? $bool . ' ' : ($isOR ? 'OR ' : ''); // Build the BOOL (AND/OR).
                
                $not = $condition['operator'] ? $condition['operator'] . ' ' : ''; // Build the operator (NOT)

                switch ($type) {
                    case 'AND_WHERE':
                    case 'OR_WHERE':
                    case 'AND_WHERE_NOT':
                    case 'OR_WHERE_NOT':
                        $stmt .= $not . $condition['column'] . ' ' . $condition['compare'] . ' ' . $condition['value'] . ' ';
    
                        break;
                    case 'AND_WHERE_BETWEEN':
                    case 'AND_WHERE_NOT_BETWEEN':
                    case 'OR_WHERE_BETWEEN':
                    case 'OR_WHERE_NOT_BETWEEN':
                        $range = explode('|', $condition['value']);
    
                        $stmt .= $condition['column'] . ' ' . $not . 'BETWEEN ' . $range[0] . ' AND ' . $range[1] . ' ';
    
                        break;
                    case 'AND_WHERE_IN':
                    case 'AND_WHERE_NOT_IN':
                    case 'OR_WHERE_IN':
                    case 'OR_WHERE_NOT_IN':
                        $stmt .= $condition['column'] . ' ' . $not . 'IN ' . $condition['value'] . ' ';
    
                        break;
                    case 'AND_HAVING':
                    case 'OR_HAVING':
                    case 'AND_NOT_HAVING':
                    case 'OR_NOT_HAVING':
                        $func = $functions && array_key_exists($condition['column'], $functions) ? $functions[$condition['column']] : '';
    
                        $stmt .= $not;
    
                        $stmt .= $func ? $func.'(' : '';
                        $stmt .= $condition['column'];
                        $stmt .= $func ? ')' : '';
    
                        $stmt .= ' ' . $condition['compare'] . ' ' . $condition['value'] . ' ';
                }
            }
        }

        return $stmt;
    }

    /**
     * Build ordering statements.
     * 
     * ORDER BY.
     */
    private function buildOrderingStmt(array $ordering): string {
        $stmt = '';
        $orderingType = $ordering['__ORDERING__'];

        unset($ordering['__ORDERING__']);

        $c = 0;
        $orderingAdded = false;
        foreach ($ordering as $key => $value) {
            if (in_array($value, $this->ordering)) {
                $stmt .= "{$key} {$value}";

                $orderingAdded = true;
            } else {
                $stmt .= $value;
            }

            $stmt .= $c !== count($ordering) - 1 ? ', ' : '';
            $c++;
        }

        // Add ordering type;
        if (!$orderingAdded) {
            $stmt .= ' ' . $orderingType;
        }

        return $stmt . ' ';
    }

    /**
     * Execute the query.
     */
    private function executeQuery(string $fetchType): array|int {
        try {
            $stmt = $this->connection->prepare( $this->query );
    
            foreach ($this->params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
    
            $stmt->execute();
    
            switch ($fetchType) {
                case 'all':
                    return $stmt->fetchAll();
    
                    break;
                case 'first':
                    return $stmt->fetch();
    
                    break;
                case 'count':
                    return (int) $stmt->rowCount();
    
                    break;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

}