<?php
namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Localization\LanguageService;

class MessageLayoutRepository extends AbstractContentRepository
{
    private string $tableName = 'tx_messenger_domain_model_messagelayout';

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function findByUid(int $uid): array|false
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where(
                $this->getQueryBuilder()
                    ->expr()
                    ->eq('uid', $this->getQueryBuilder()->expr()->literal($uid)),
            );

        return $query->execute()->fetchOne();
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
