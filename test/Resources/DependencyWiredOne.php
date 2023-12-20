<?php
declare(strict_types=1);

namespace PChouse\Resources;

use PChouse\Di\IDIEvents;
use PChouse\Di\Inject;

class DependencyWiredOne implements IDIEvents, IDependencyWiredOne
{

    private bool $afterInstanceCreatedInit = false;

    private bool $beforeReturnInstanceInit = false;

    #[Inject]
    public function __construct(private readonly IDependencyWiredSingleton $dependencySingleton)
    {
    }

    public function isAfterInstanceCreatedInit(): bool
    {
        return $this->afterInstanceCreatedInit;
    }

    public function isBeforeReturnInstanceInit(): bool
    {
        return $this->beforeReturnInstanceInit;
    }

    public function getDependencyWiredSingleton(): IDependencyWiredSingleton
    {
        return $this->dependencySingleton;
    }

    public function afterInstanceCreated(): void
    {
        $this->afterInstanceCreatedInit = true;
    }

    /**
     * @throws \Exception
     */
    public function beforeReturnInstance(): void
    {
        if (!$this->afterInstanceCreatedInit) {
            throw new \Exception(
                "The AfterInstanceCreated did not run"
            );
        }
        $this->beforeReturnInstanceInit = true;
    }
}
