<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal active-record-ish base class. Every concrete model just sets
 * \$table (and optionally \$primaryKey) and inherits CRUD + simple finders.
 * Every statement is a prepared statement - no raw user input ever reaches
 * the query string.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    /**
     * Set to true in a subclass only when its table actually has a
     * deleted_at column (check database/issp_database_schema.sql). all()
     * and softDelete() both key off this - calling softDelete() on a model
     * where this is false will fail at the database level, by design.
     */
    protected static bool $softDeletes = false;

    public static function all(string $orderBy = 'id DESC'): array
    {
        $db    = Database::connection();
        $where = static::$softDeletes ? 'WHERE deleted_at IS NULL ' : '';
        $sql   = sprintf('SELECT * FROM `%s` %sORDER BY %s', static::$table, $where, $orderBy);
        $result = $db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function find(int|string $id): ?array
    {
        $db   = Database::connection();
        $stmt = $db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = ? LIMIT 1', static::$table, static::$primaryKey));
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $db   = Database::connection();
        $stmt = $db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = ? LIMIT 1', static::$table, $column));
        $stmt->bind_param('s', $value);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /** @return array<int, array<string, mixed>> */
    public static function whereAll(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $db   = Database::connection();
        $stmt = $db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = ? ORDER BY %s', static::$table, $column, $orderBy));
        $stmt->bind_param('s', $value);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function create(array $data): int
    {
        $db      = Database::connection();
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList   = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));

        $stmt  = $db->prepare(sprintf('INSERT INTO `%s` (%s) VALUES (%s)', static::$table, $columnList, $placeholders));
        $types = str_repeat('s', count($columns));
        $stmt->bind_param($types, ...array_values($data));
        $stmt->execute();
        $insertId = (int) $db->insert_id;
        $stmt->close();
        return $insertId;
    }

    public static function update(int|string $id, array $data): bool
    {
        $db   = Database::connection();
        $sets = implode(', ', array_map(fn ($c) => "`{$c}` = ?", array_keys($data)));
        $stmt = $db->prepare(sprintf('UPDATE `%s` SET %s WHERE `%s` = ?', static::$table, $sets, static::$primaryKey));

        $types  = str_repeat('s', count($data)) . 's';
        $values = [...array_values($data), $id];
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function delete(int|string $id): bool
    {
        $db   = Database::connection();
        $stmt = $db->prepare(sprintf('DELETE FROM `%s` WHERE `%s` = ?', static::$table, static::$primaryKey));
        $stmt->bind_param('s', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function softDelete(int|string $id): bool
    {
        return static::update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Generic paginated + filtered + searched listing, shared by every
     * admin list page. $filters is column => exact-match value (a null or
     * '' value is ignored, so controllers can pass raw query-string input
     * straight through without checking it first). $search does a LIKE
     * scan across $searchColumns, OR'd together. Honors $softDeletes the
     * same way all() does.
     *
     * @param array<string, mixed> $filters
     * @param array<int, string> $searchColumns
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(
        int $page,
        int $perPage,
        array $filters = [],
        ?string $search = null,
        array $searchColumns = [],
        string $orderBy = 'id DESC'
    ): array {
        [$where, $types, $params] = static::filterClause($filters, $search, $searchColumns);

        $offset = max(0, ($page - 1) * $perPage);
        $sql = sprintf('SELECT * FROM `%s` %s ORDER BY %s LIMIT ? OFFSET ?', static::$table, $where, $orderBy);

        $db   = Database::connection();
        $stmt = $db->prepare($sql);
        $types   .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /** Total rows matching the same $filters/$search paginate() would use - for page-count math. */
    public static function count(array $filters = [], ?string $search = null, array $searchColumns = []): int
    {
        [$where, $types, $params] = static::filterClause($filters, $search, $searchColumns);
        $db  = Database::connection();
        $sql = sprintf('SELECT COUNT(*) AS total FROM `%s` %s', static::$table, $where);

        if ($types === '') {
            $row = $db->query($sql)->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['total'] ?? 0);
    }

    /** @return array{0: string, 1: string, 2: array<int, mixed>} [whereSql, bindTypes, bindParams] */
    private static function filterClause(array $filters, ?string $search, array $searchColumns): array
    {
        $conditions = [];
        $types      = '';
        $params     = [];

        if (static::$softDeletes) {
            $conditions[] = '`deleted_at` IS NULL';
        }

        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value)) {
                if (empty($value)) {
                    continue;
                }
                $placeholders  = implode(', ', array_fill(0, count($value), '?'));
                $conditions[]  = sprintf('`%s` IN (%s)', $column, $placeholders);
                $types        .= str_repeat('s', count($value));
                array_push($params, ...array_values($value));
                continue;
            }
            $conditions[] = sprintf('`%s` = ?', $column);
            $types       .= 's';
            $params[]     = $value;
        }

        if ($search !== null && $search !== '' && !empty($searchColumns)) {
            $like = '%' . $search . '%';
            $likeConditions = [];
            foreach ($searchColumns as $column) {
                $likeConditions[] = sprintf('`%s` LIKE ?', $column);
                $types           .= 's';
                $params[]         = $like;
            }
            $conditions[] = '(' . implode(' OR ', $likeConditions) . ')';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $types, $params];
    }
}
