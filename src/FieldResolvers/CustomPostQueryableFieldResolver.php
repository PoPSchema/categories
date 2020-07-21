<?php

declare(strict_types=1);

namespace PoP\Categories\FieldResolvers;

use PoP\Categories\ComponentConfiguration;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\Categories\TypeResolvers\CategoryTypeResolver;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\CustomPosts\FieldInterfaceResolvers\IsCustomPostFieldInterfaceResolver;
use PoP\ComponentModel\FieldResolvers\AbstractQueryableFieldResolver;

class CustomPostQueryableFieldResolver extends AbstractQueryableFieldResolver
{
    public static function getClassesToAttachTo(): array
    {
        return [
            IsCustomPostFieldInterfaceResolver::class,
        ];
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
            'categories',
            'categoryCount',
        ];
    }

    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $types = [
            'categories' => TypeCastingHelpers::makeArray(SchemaDefinition::TYPE_ID),
            'categoryCount' => SchemaDefinition::TYPE_INT,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }

    public function isSchemaFieldResponseNonNullable(TypeResolverInterface $typeResolver, string $fieldName): bool
    {
        $nonNullableFieldNames = [
            'categories',
            'categoryCount',
        ];
        if (in_array($fieldName, $nonNullableFieldNames)) {
            return true;
        }
        return parent::isSchemaFieldResponseNonNullable($typeResolver, $fieldName);
    }

    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
            'categories' => $translationAPI->__('Categories added to this post', 'pop-categories'),
            'categoryCount' => $translationAPI->__('Number of categories added to this post', 'pop-categories'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }

    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        switch ($fieldName) {
            case 'categories':
            case 'categoryCount':
                return array_merge(
                    $schemaFieldArgs,
                    $this->getFieldArgumentsSchemaDefinitions($typeResolver, $fieldName)
                );
        }
        return $schemaFieldArgs;
    }

    public function enableOrderedSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): bool
    {
        switch ($fieldName) {
            case 'categories':
            case 'categoryCount':
                return false;
        }
        return parent::enableOrderedSchemaFieldArgs($typeResolver, $fieldName);
    }

    protected function getFieldDefaultFilterDataloadingModule(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []): ?array
    {
        switch ($fieldName) {
            case 'categoryCount':
                return [\PoP_Categories_Module_Processor_FieldDataloads::class, \PoP_Categories_Module_Processor_FieldDataloads::MODULE_DATALOAD_RELATIONALFIELDS_CATEGORYCOUNT];
        }
        return parent::getFieldDefaultFilterDataloadingModule($typeResolver, $fieldName, $fieldArgs);
    }

    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $categoryapi = \PoP\Categories\FunctionAPIFactory::getInstance();
        $post = $resultItem;
        switch ($fieldName) {
            case 'categories':
                $query = [
                    'limit' => ComponentConfiguration::getCategoryListDefaultLimit(),
                ];
                $options = [
                    'return-type' => POP_RETURNTYPE_IDS,
                ];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $categoryapi->getCustomPostCategories(
                    $typeResolver->getID($post),
                    $query,
                    $options
                );
            case 'categoryCount':
                $options = [];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $categoryapi->getCustomPostCategoryCount(
                    $typeResolver->getID($post),
                    [],
                    $options
                );
        }

        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }

    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []): ?string
    {
        switch ($fieldName) {
            case 'categories':
                return CategoryTypeResolver::class;
        }

        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName, $fieldArgs);
    }
}
