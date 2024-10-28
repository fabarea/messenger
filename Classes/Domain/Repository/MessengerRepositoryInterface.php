<?php

namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface MessengerRepositoryInterface
{
    public function findByUid(int $uid): array;

    public function findAll(): array;

    public function findByDemand(array $demand = [], array $orderings = [], int $offset = 0, int $limit = 0): array;

    public function deleteByUids(array $uids): int;
}
