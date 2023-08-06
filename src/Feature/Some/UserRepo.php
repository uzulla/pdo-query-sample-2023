<?php
declare(strict_types=1);

namespace SomeSample\Feature\Some;

use PDO;
use SomeSample\Repo\DB;
use SomeSample\Repo\Exception\DeleteException;
use SomeSample\Repo\Exception\MissingException;
use SomeSample\Repo\Exception\UpdateException;
use SomeSample\Repo\QueryHelperTrait;

class UserRepo extends DB
{
    use QueryHelperTrait;

    public function create(UserEntity $user): int
    {
        $p = $this->getPdo();

        $p->beginTransaction();

        $sql = '
            INSERT INTO user 
                (`name`, `updated_at`, `created_at`) 
            VALUES
                (:name, now(), now())
        ';
        $stmt = $p->prepare($sql);
        $stmt->bindValue(':name', $user->name, PDO::PARAM_STR);
        $stmt->execute();
        $user_id = (int)$p->lastInsertId();

        $sql = '
            INSERT INTO user_point
                (`user_id`) 
            VALUES
                (:user_id)
        ';
        $stmt = $p->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $p->commit();

        return $user_id;
    }

    public function getById(int $user_id): UserEntity
    {
        $p = $this->getPdo();

        $sql = '
            SELECT
                u.name as name,
                up.point as point,
                u.created_at as created_at,
                u.created_at as updated_at,
                u.id as id
            FROM user u
            INNER JOIN dev_db.user_point up on u.id = up.user_id 
            WHERE u.id = :user_id
        ';

        $stmt = $p->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);

        $stmt->execute();
        if (($res = $stmt->fetch(PDO::FETCH_ASSOC)) === false) throw new MissingException("missing user_id: {$user_id}");
        return new UserEntity(...$res);
    }

    /**
     * @return UserEntity[]
     */
    public function getsAll(): array
    {
        $p = $this->getPdo();

        $sql = '
            SELECT
                u.id as id,
                u.name as name,
                u.created_at as created_at,
                u.created_at as updated_at,
                up.point as point
            FROM user u
            INNER JOIN user_point up ON u.id = up.user_id 
        ';

        $stmt = $p->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, UserEntity::class);
        $stmt->execute();

        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new UserEntity(...$row);
        }
        return $list;
    }

    public function update(UserEntity $user): bool
    {
        $p = $this->getPdo();
        $p->beginTransaction();

        $sql = '
            UPDATE user
            SET
                name = :name,
                updated_at = now()
            WHERE id = :id';
        $stmt = $p->prepare($sql);
        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $user->name, PDO::PARAM_STR);
        if (true !== $stmt->execute()) throw new UpdateException("update user failed id: {$user->id}");

        $sql = '
            UPDATE user_point
            SET
                point = :point
            WHERE user_id = :user_id';
        $stmt = $p->prepare($sql);
        $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':point', $user->point, PDO::PARAM_INT);
        if (true !== $stmt->execute()) throw new UpdateException("update user_point failed id: {$user->id}");

        return $p->commit();
    }

    public function delete(UserEntity $user): bool
    {
        $p = $this->getPdo();
        $p->beginTransaction();

        $sql = 'DELETE FROM user_point WHERE user_id = :user_id';
        $stmt = $p->prepare($sql);
        $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
        if (true !== $stmt->execute()) throw new DeleteException("delete user_point failed id: {$user->id}");

        $sql = 'DELETE FROM user WHERE id = :id';
        $stmt = $p->prepare($sql);
        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        if (true !== $stmt->execute()) throw new DeleteException("delete user failed id: {$user->id}");

        return $p->commit();
    }
}