<?php

declare(strict_types=1);

namespace Warp;

use Nette\Caching\Storage;
use Nette\Caching\Storages\MemoryStorage;

class ProxyBuilder
{
    private Storage $storage;
    private ProxyFactory $proxyFactory;
    private array $proxyDictionary = [];

    public function __construct(
        private MappingManager $mappingManager,
        ?Storage               $storage = null
    )
    {
        if (is_null($storage)) {
            $storage = new MemoryStorage();
        }
        $this->storage = $storage;
        $this->proxyFactory = new ProxyFactory($this->mappingManager);
    }

    public function build(string $className): object
    {
        if (isset($this->proxyDictionary[$className])) {
            return new $this->proxyDictionary[$className]();
        }
        $proxyClassName = $this->proxyFactory->getProxyClassName($className);
        if (!class_exists($proxyClassName)) {
            $proxyClassPath = $this->storage->read($proxyClassName);
            if (!$proxyClassPath) {
                $class = $this->proxyFactory->create($className);
                $proxyClassPath = rtrim($this->mappingManager->getProxyClassBasePath(), '/')
                    . '/' . $proxyClassName . '.php';
                file_put_contents($proxyClassPath, "<?php \n" . $class['phpClassContent']);
                $this->storage->write($className, $proxyClassPath, []);
            }
            require $proxyClassPath;
            $this->proxyDictionary[$className] = $proxyClassName;
        }
        return new $proxyClassName();
    }
}