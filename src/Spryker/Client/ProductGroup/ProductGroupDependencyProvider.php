<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductGroup;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\ProductGroup\Dependency\Client\ProductGroupToProductBridge;
use Spryker\Client\ProductGroup\Dependency\Client\ProductGroupToStorageBridge;

class ProductGroupDependencyProvider extends AbstractDependencyProvider
{

    const CLIENT_STORAGE = 'CLIENT_STORAGE';
    const CLIENT_PRODUCT = 'CLIENT_PRODUCT';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $this->provideStorageClient($container);
        $this->provideProductClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return void
     */
    protected function provideStorageClient(Container $container)
    {
        $container[self::CLIENT_STORAGE] = function (Container $container) {
            return new ProductGroupToStorageBridge($container->getLocator()->storage()->client());
        };
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return void
     */
    protected function provideProductClient(Container $container)
    {
        $container[self::CLIENT_PRODUCT] = function (Container $container) {
            return new ProductGroupToProductBridge($container->getLocator()->product()->client());
        };
    }

}