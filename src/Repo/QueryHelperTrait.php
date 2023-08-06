<?php
declare(strict_types=1);

namespace SomeSample\Repo;

use InvalidArgumentException;
use PDO;

// 強くMysqlとペッグしている実装なので注意
trait QueryHelperTrait
{
    /**
     * @template T
     * @param class-string<T> $class_name
     * @return T|null
     */
    public function getOrNullBySomeCol(
        string $class_name,
        string $table_name,
        string $where_col,
        string|int|bool $where_val,
        int $bind_type = PDO::PARAM_STR,
        ?PDO $pdo = null
    ): object|null
    {
        static::throwWhenContainNotSafeStr($table_name);
        static::throwWhenContainNotSafeStr($where_col);

        $pdo ??= $this->getPdo();

        $stmt = $pdo->prepare("SELECT * FROM `{$table_name}` WHERE {$where_col} = :{$where_col};");
        $stmt->bindValue($where_col, $where_val, $bind_type);
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_CLASS, $class_name);
        if (($res = $stmt->fetch()) === false) return null;
        return $res;
    }

    public static function throwWhenContainNotSafeStr(string $str): void
    {
        if (preg_match('/[^a-zA-Z0-9\-_]/u', $str)) throw new InvalidArgumentException("Not safe string");
    }

    /**
     * @param $list array{
     *     0: string,
     *     1: string|int|bool,
     *     2: int
     * }
     * 0: column name, 1: value, 2: PDO::PARAM_*
     */
    public function insertByList(
        string $table_name,
        array $list,
        PDO $pdo = null
    ): string|false
    {
        static::throwWhenContainNotSafeStr($table_name);

        $pdo ??= $this->getPdo();

        # build sql
        $col_csv = "";
        foreach ($list as $row) {
            static::throwWhenContainNotSafeStr($row[0]);
            $col_csv .= "`$row[0]`, ";
        }
        $col_csv = rtrim($col_csv, ', ');

        $value_placeholder_csv = "";
        foreach ($list as $row) {
            $value_placeholder_csv .= ":{$row[0]}, ";
        }
        $value_placeholder_csv = rtrim($value_placeholder_csv, ', ');
        $sql = "INSERT INTO `{$table_name}` ( {$col_csv} ) VALUES ( {$value_placeholder_csv} );";

        # bind value.
        $stmt = $pdo->prepare($sql);
        foreach ($list as $row) {
            $stmt->bindValue($row[0], $row[1], $row[2]);
        }

        $stmt->execute(); # throw exception when error.
        return $pdo->lastInsertId();
    }

    /**
     * @param $list array{
     *     0: string,
     *     1: string|int|bool,
     *     2: int
     * }
     * 0: column name, 1: value, 2: PDO::PARAM_*
     */
    function updateByList(
        string $table_name,
        array $list,
        string $pkey_col,
        int|string $pkey_val,
        int $id_pdo_param_type,
        PDO $pdo = null
    ): bool
    {
        static::throwWhenContainNotSafeStr($table_name);
        static::throwWhenContainNotSafeStr($pkey_col);

        $pdo ??= $this->getPdo();

        # build sql
        $update_sql = " SET ";
        foreach ($list as $row) {
            if ($row[0] === $pkey_col) {
                continue;
            }
            $update_sql .= "`$row[0]`=:$row[0], ";
        }
        $update_sql = rtrim($update_sql, ', ');
        $sql = "UPDATE `{$table_name}` {$update_sql} WHERE `{$pkey_col}` = :{$pkey_col}";

        # bind val.
        $stmt = $pdo->prepare($sql);
        # set pkey col.
        $stmt->bindValue($pkey_col, $pkey_val, $id_pdo_param_type);
        # set other col.
        foreach ($list as $row) {
            if ($row[0] === $pkey_col) {
                continue;
            } else {
                $stmt->bindValue($row[0], $row[1], $row[2]);
            }
        }

        $stmt->execute(); # throw exception when error.
        return $stmt->rowCount() === 1;
    }

    function deleteByPkey(
        string $table_name,
        string $pkey_col,
        int $pkey_val,
        int $pdo_param_type,
        PDO $pdo = null
    ): bool
    {
        static::throwWhenContainNotSafeStr($table_name);
        static::throwWhenContainNotSafeStr($pkey_col);

        $pdo ??= $this->getPdo();

        # build sql
        $sql = "DELETE FROM `{$table_name}` WHERE `{$pkey_col}` = :{$pkey_col}";

        # bind val.
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue($pkey_col, $pkey_val, $pdo_param_type);

        $stmt->execute(); # throw exception when error.
        return $stmt->rowCount() === 1;
    }
}
