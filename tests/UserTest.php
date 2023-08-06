<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SomeSample\Feature\Some\UserEntity;
use SomeSample\Feature\Some\UserRepo;
use SomeSample\Repo\Exception\MissingException;

class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user_name = "test_user_" . time();
        $user = new UserEntity(
            $user_name
        );

        $repo = new UserRepo();

        $user_id = $repo->create($user);

        $this->assertIsInt($user_id);

        $created_user = $repo->getById($user_id);

        $this->assertInstanceOf(UserEntity::class, $created_user);
        $this->assertSame($user_name, $created_user->name);
    }

    public function testGetUsers(): void
    {
        $repo = new UserRepo();
        $users = $repo->getsAll();

        $this->assertIsArray($users);
        $this->assertInstanceOf(UserEntity::class, $users[0]);
    }

    public function testUpdateSomeUser(): void
    {
        $repo = new UserRepo();
        $user = $repo->getById(1);
        $user_name = "test_user_" . time();
        $point = time();

        $user->name = $user_name;
        $user->point = $point;

        $repo->update($user);

        $updated_user = $repo->getById(1);

        $this->assertSame($user_name, $updated_user->name);
        $this->assertSame($point, $updated_user->point);
    }

    public function testDeleteSomeUser(): void
    {
        $repo = new UserRepo();

        $user = new UserEntity('test_user_' . time());

        $user_id = $repo->create($user);

        $created_user = $repo->getById($user_id);

        $this->assertInstanceOf(UserEntity::class, $created_user);

        $repo->delete($created_user);

        try {
            $repo->getById($user_id);
            $this->fail("deleted user should not be found");
        } catch (MissingException $e) {
            // it is ok
            $this->assertInstanceOf(MissingException::class, $e);
        }
    }
}