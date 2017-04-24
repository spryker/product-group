<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductGroup\Business\Model;

use Generated\Shared\Transfer\ProductGroupTransfer;
use Orm\Zed\ProductGroup\Persistence\SpyProductAbstractGroup;
use Orm\Zed\ProductGroup\Persistence\SpyProductGroup;
use Spryker\Zed\ProductGroup\Business\Exception\ProductGroupNotFoundException;
use Spryker\Zed\ProductGroup\Persistence\ProductGroupQueryContainerInterface;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;

class ProductGroupExpander implements ProductGroupExpanderInterface
{

    use DatabaseTransactionHandlerTrait;

    /**
     * @var \Spryker\Zed\ProductGroup\Persistence\ProductGroupQueryContainerInterface
     */
    protected $productGroupQueryContainer;

    /**
     * @var \Spryker\Zed\ProductGroup\Business\Model\ProductGroupTouchInterface
     */
    protected $productGroupTouch;

    /**
     * @param \Spryker\Zed\ProductGroup\Persistence\ProductGroupQueryContainerInterface $productGroupQueryContainer
     * @param \Spryker\Zed\ProductGroup\Business\Model\ProductGroupTouchInterface $productGroupTouch
     */
    public function __construct(ProductGroupQueryContainerInterface $productGroupQueryContainer, ProductGroupTouchInterface $productGroupTouch)
    {
        $this->productGroupQueryContainer = $productGroupQueryContainer;
        $this->productGroupTouch = $productGroupTouch;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductGroupTransfer $productGroupTransfer
     *
     * @return \Generated\Shared\Transfer\ProductGroupTransfer
     */
    public function extendProductGroup(ProductGroupTransfer $productGroupTransfer)
    {
        $this->assertProductGroupForExtension($productGroupTransfer);

        return $this->handleDatabaseTransaction(function () use ($productGroupTransfer) {
            return $this->executeExtendProductGroupTransaction($productGroupTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\ProductGroupTransfer $productGroupTransfer
     *
     * @return void
     */
    protected function assertProductGroupForExtension(ProductGroupTransfer $productGroupTransfer)
    {
        $productGroupTransfer->requireIdProductGroup();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductGroupTransfer $productGroupTransfer
     *
     * @return \Generated\Shared\Transfer\ProductGroupTransfer
     */
    protected function executeExtendProductGroupTransaction(ProductGroupTransfer $productGroupTransfer)
    {
        $productGroupEntity = $this->findProductGroupEntity($productGroupTransfer);

        $this->touchProductGroup($productGroupTransfer);

        return $this->saveProductGroupEntity($productGroupEntity, $productGroupTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductGroupTransfer $productGroupTransfer
     *
     * @throws \Spryker\Zed\ProductGroup\Business\Exception\ProductGroupNotFoundException
     *
     * @return \Orm\Zed\ProductGroup\Persistence\SpyProductGroup
     */
    protected function findProductGroupEntity(ProductGroupTransfer $productGroupTransfer)
    {
        $productGroupEntity = $this->productGroupQueryContainer
            ->queryProductGroupById($productGroupTransfer->getIdProductGroup())
            ->findOne();

        if (!$productGroupEntity) {
            throw new ProductGroupNotFoundException(sprintf(
                'Product group with ID "%d" not found.',
                $productGroupTransfer->getIdProductGroup()
            ));
        }

        return $productGroupEntity;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductGroupTransfer $productGroupTransfer
     *
     * @return void
     */
    protected function touchProductGroup(ProductGroupTransfer $productGroupTransfer)
    {
        $this->productGroupTouch->touchProductGroupActive($productGroupTransfer);
        $this->productGroupTouch->touchProductAbstractGroupsActive($productGroupTransfer);
    }

    /**
     * @param \Orm\Zed\ProductGroup\Persistence\SpyProductGroup $productGroupEntity
     * @param \Generated\Shared\Transfer\ProductGroupTransfer $productGroupTransfer
     *
     * @return \Generated\Shared\Transfer\ProductGroupTransfer
     */
    protected function saveProductGroupEntity(SpyProductGroup $productGroupEntity, ProductGroupTransfer $productGroupTransfer)
    {
        $idProductAbstracts = $productGroupTransfer->getIdProductAbstracts();
        $existingProductAbstractGroups = $this->getExistingIdProductAbstracts($productGroupEntity);
        $productGroupTransfer->setIdProductAbstracts($existingProductAbstractGroups);

        $position = count($existingProductAbstractGroups);
        foreach ($idProductAbstracts as $idProductAbstract) {
            if (in_array($idProductAbstract, $existingProductAbstractGroups)) {
                continue;
            }

            $productAbstractGroupEntity = $this->createProductAbstractGroupEntity($idProductAbstract, $position);
            $productGroupEntity->addSpyProductAbstractGroup($productAbstractGroupEntity);
            $productGroupTransfer->addIdProductAbstract($idProductAbstract);
            $position++;
        }

        $productGroupEntity->save();

        return $productGroupTransfer;
    }

    /**
     * @param \Orm\Zed\ProductGroup\Persistence\SpyProductGroup $productGroupEntity
     *
     * @return array
     */
    protected function getExistingIdProductAbstracts(SpyProductGroup $productGroupEntity)
    {
        $existingProductAbstractGroups = [];

        foreach ($productGroupEntity->getSpyProductAbstractGroups() as $productAbstractGroupEntity) {
            $existingProductAbstractGroups[] = $productAbstractGroupEntity->getFkProductAbstract();
        }

        return $existingProductAbstractGroups;
    }

    /**
     * @param int $idProductAbstract
     * @param int $position
     *
     * @return \Orm\Zed\ProductGroup\Persistence\SpyProductAbstractGroup
     */
    protected function createProductAbstractGroupEntity($idProductAbstract, $position)
    {
        $productAbstractGroupEntity = new SpyProductAbstractGroup();
        $productAbstractGroupEntity
            ->setFkProductAbstract($idProductAbstract)
            ->setPosition($position);

        return $productAbstractGroupEntity;
    }

}