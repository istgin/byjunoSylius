<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Repository;


trait ByjunoLogTrait
{
    /** @var ByjunoLogRepository */
    protected $byjunoLogRepository;

    public function __construct(ByjunoLogRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function saveLog(): void
    {

    }
}
