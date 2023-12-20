<?php

namespace PChouse\Resources;

use PChouse\Di\IDIEvents;
use PChouse\Di\Inject;

class Dependency implements IDIEvents, IDependency
{

    private bool $afterInstanceCreatedInit = false;

    private bool $beforeReturnInstanceInit = false;

    #[Inject]
    private IDependencyWiredOne $dependencyWiredOne;

    private int $rand = 0;

    #[Inject]
    public function __construct(private IDependencyWiredTwo $dependencyWiredTwo)
    {
    }
    public function getDependencyWiredOne(): IDependencyWiredOne
    {
        return $this->dependencyWiredOne;
    }
    public function setDependencyWiredOne(IDependencyWiredOne $dependencyWiredOne): void
    {
        $this->dependencyWiredOne = $dependencyWiredOne;
    }
    public function getDependencyWiredTwo(): IDependencyWiredTwo
    {
        return $this->dependencyWiredTwo;
    }
    public function setDependencyWiredTwo(IDependencyWiredTwo $dependencyWiredTwo): void
    {
        $this->dependencyWiredTwo = $dependencyWiredTwo;
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
    public function isAfterInstanceCreatedInit(): bool
    {
        return $this->afterInstanceCreatedInit;
    }
    public function isBeforeReturnInstanceInit(): bool
    {
        return $this->beforeReturnInstanceInit;
    }
    public function getRand(): int
    {
        return $this->rand;
    }
    public function setRand(int $rand):void
    {
        $this->rand = $rand;
    }
}
