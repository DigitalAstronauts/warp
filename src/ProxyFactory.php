<?php

declare(strict_types=1);

namespace Warp;

use Nette\PhpGenerator\ClassType;
use Warp\Mapping\JoinColumn;

class ProxyFactory
{

    public function __construct(
        private MappingManager $mappingManager
    )
    {
    }

    public function create(string $entityClass): array
    {
        $entityMapping = $this->mappingManager->getMapping($entityClass);
        if(!$entityMapping->isEntity()) {
            throw new \InvalidArgumentException(
                "Given class `{$entityClass}` is not valid entity;"
            );
        }
        $proxyClassName = $this->getProxyClassName($entityClass);

        $class = new ClassType();
        $class->setName($proxyClassName);
        $class->setExtends($entityClass);
        $class->addTrait(EntityProxyTrait::class);

        foreach ($entityMapping->columns as $column) {
            if ($column instanceof JoinColumn) {
                $methodName = 'get' . ucfirst($column->propertyName);
                if (method_exists($entityClass, $methodName)) {
                    $method = $class->addMethod($methodName);
                    $methodBody = <<<MTHD
\$this->initializeProperty(?);
return parent::?();
MTHD;
                    $method->addBody($methodBody, [$column->propertyName, $methodName]);
                    $reflectionMethod = new \ReflectionMethod($entityClass, $methodName);
                    $method->setReturnType($reflectionMethod->getReturnType()->getName());
                }
            }
        }
        return [
            'proxyClassName' => $proxyClassName,
            'phpClassContent' => (string)$class,
        ];
    }

    public function getProxyClassName(string $entityClass): string
    {
        return str_replace('\\', '_', $entityClass) . '__WarpProxy';
    }
}