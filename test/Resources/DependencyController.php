<?php
declare(strict_types=1);

namespace PChouse\Resources;

use PChouse\Di\IDIEvents;
use PChouse\Di\Inject;

class DependencyController implements IDIEvents, IDependencyController
{

    private bool $afterInstanceCreatedInit = false;

    private bool $beforeReturnInstanceInit = false;

    #[Inject]
    public function __construct(private readonly IDependencyWiredSingleton $dependencyWiredSingleton)
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

    public function getDependencyWiredTree(): IDependencyWiredSingleton
    {
        return $this->dependencyWiredSingleton;
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
