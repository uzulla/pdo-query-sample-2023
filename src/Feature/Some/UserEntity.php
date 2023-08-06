<?php
declare(strict_types=1);

namespace SomeSample\Feature\Some;

use DateTimeImmutable;
use Exception;
use LogicException;
use SomeSample\Repo\Exception\HydrateException;

class UserEntity
{
    public DateTimeImmutable $updatedAt;
    public DateTimeImmutable $createdAt;

    /**
     * @throws HydrateException
     */
    public function __construct(
        public string $name,
        public int $point = 0,
        string|DateTimeImmutable|null $created_at = null,
        string|DateTimeImmutable|null $updated_at = null,
        public int|null $id = null
    )
    {
        $this->createdAt = $this->returnDateTimeImmutable($created_at);
        $this->updatedAt = $this->returnDateTimeImmutable($updated_at);
    }

    /**
     * 引数がDateTimeImmutableかStringかNullの場合に応じて、DateTimeImmutableを返す
     * @throws HydrateException
     */
    private function returnDateTimeImmutable(string|DateTimeImmutable|null $date): DateTimeImmutable
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        } else if (is_string($date)) {
            try {
                return new DateTimeImmutable($date);
            } catch (Exception $e) {
                throw new HydrateException("DateTime hydrate failed: {$e->getMessage()}");
            }
        } else if (is_null($date)) {
            return new DateTimeImmutable(); // nullの場合は現在の日時で作成する
        } else {
            throw new LogicException("createdAt is invalid");
        }
    }

    // for some short hands.
    // public function getArrayForUpdate(): array
    // {
    //     return [
    //         // id is pkey. so it is not included.
    //         ["name", $this->name, PDO::PARAM_STR],
    //         ["point", $this->point, PDO::PARAM_INT],
    //         ["created_at", $this->createdAt->format(DATE_ATOM), PDO::PARAM_STR],
    //         ["updated_at", $this->updatedAt->format(DATE_ATOM), PDO::PARAM_STR],
    //     ];
    // }
}
