<?php

declare(strict_types=1);

namespace PoPSchema\Categories\Facades;

use PoPSchema\Categories\TypeAPIs\CategoryTypeAPIInterface;
use PoP\Root\Container\ContainerBuilderFactory;

class CategoryTypeAPIFacade
{
    public static function getInstance(): CategoryTypeAPIInterface
    {
        return ContainerBuilderFactory::getInstance()->get('category_type_api');
    }
}
