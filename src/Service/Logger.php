<?php

declare(strict_types=1);

namespace App\Service;

use App\Classes\ServiceTokenUserInterface;
use App\Repository\AuditLogRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Classes\SessionUserInterface;

/**
 * Class Logger
 */
class Logger
{
    protected ?int $userId = null;

    protected ?int $tokenId = null;

    protected array $entries = [];

    /**
     * Set the userId from injected security context
     */
    public function __construct(
        TokenStorageInterface $securityTokenStorage,
        protected AuditLogRepository $repository,
        protected LoggerInterface $frameworkLogger
    ) {
        if (null !== $securityTokenStorage->getToken()) {
            /** @var SessionUserInterface $sessionUser */
            $sessionUser = $securityTokenStorage->getToken()->getUser();
            if ($sessionUser instanceof SessionUserInterface) {
                $this->userId = $sessionUser->getId();
            } elseif ($sessionUser instanceof ServiceTokenUserInterface) {
                $this->tokenId = $sessionUser->getId();
            }
        }
    }

    /**
     * Log an action
     *
     * @param string $action
     * @param string $objectId
     * @param string $objectClass
     * @param string $valuesChanged
     * @param bool $andFlush
     */
    public function log(
        $action,
        $objectId,
        $objectClass,
        $valuesChanged,
        $andFlush = true
    ) {
        if (!$this->userId && !$this->tokenId) {
            throw new Exception('Attempted to log something but there is no authenticated user.');
        }
        $log = [
            'action' => $action,
            'objectId' => $objectId,
            'objectClass' => $objectClass,
            'valuesChanged' => $valuesChanged,
            'userId' => $this->userId,
            'tokenId' => $this->tokenId,
        ];
        $this->entries[] = $log;

        if ($andFlush) {
            $this->flush();
        }
    }

    /**
     * Write logs to the DB
     */
    public function flush()
    {
        try {
            $this->repository->writeLogs($this->entries);
            $this->entries = [];
        } catch (Exception $e) {
            $this->frameworkLogger->alert('Unable to write logs: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
