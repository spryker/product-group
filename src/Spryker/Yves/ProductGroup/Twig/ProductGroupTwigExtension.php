<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\ProductGroup\Twig;

use Spryker\Client\ProductGroup\ProductGroupClientInterface;
use Spryker\Shared\Twig\TwigExtension;
use Twig\Environment;
use Twig\TwigFunction;

class ProductGroupTwigExtension extends TwigExtension
{
    /**
     * @var string
     */
    public const FUNCTION_NAME_PRODUCT_GROUP_ITEMS = 'spyProductGroupItems';

    /**
     * @var \Spryker\Client\ProductGroup\ProductGroupClientInterface
     */
    protected $productGroupClient;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param \Spryker\Client\ProductGroup\ProductGroupClientInterface $productGroupClient
     * @param string $locale
     */
    public function __construct(ProductGroupClientInterface $productGroupClient, string $locale)
    {
        $this->productGroupClient = $productGroupClient;
        $this->locale = $locale;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(static::FUNCTION_NAME_PRODUCT_GROUP_ITEMS, [$this, 'renderProductGroupItems'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    /**
     * @param \Twig\Environment $twig
     * @param int $idProductAbstract
     * @param string $template
     *
     * @return string
     */
    public function renderProductGroupItems(Environment $twig, $idProductAbstract, $template)
    {
        $productGroupItems = $this->productGroupClient->findProductGroupItemsByIdProductAbstract($idProductAbstract, $this->locale);

        if (!$productGroupItems) {
            return '';
        }

        return $twig->render($template, [
            'productGroupItems' => $productGroupItems,
        ]);
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return string
     */
    protected function getLocale()
    {
        return $this->locale;
    }
}
