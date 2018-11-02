<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use WoohooLabs\Yin\JsonApi\Schema\Links;
<?php
foreach ($associations as $association) {
    if (in_array($association['type'], $to_many_types)) {
        $useManyRelation = true;
    } else {
        $useOneRelation = true;
    }
    echo 'use ' . $association['target_entity'] . ';' . PHP_EOL;
}
echo isset($useManyRelation) ? 'use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;' . PHP_EOL : '';
echo isset($useOneRelation) ? 'use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;' . PHP_EOL : '';
?>
use Paknahad\JsonApiBundle\ResourceTransformer\AbstractResourceTransformer;
use Paknahad\JsonApiBundle\Helper\InputOutputManager;

/**
 * <?= $entity_class_name ?> Resource Transformer.
 */
class <?= $entity_class_name ?>ResourceTransformer extends AbstractResourceTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getType($<?= $entity_var_name ?>): string
    {
        return '<?= $entity_type_var_plural ?>';
    }

    /**
     * {@inheritdoc}
     */
    public function getId($<?= $entity_var_name ?>): string
    {
        return (string) $<?= $entity_var_name ?>->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta($<?= $entity_var_name ?>): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks($<?= $entity_var_name ?>): ?Links
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributes($<?= $entity_var_name ?>): array
    {
        return [<?php
        foreach ($fields as $field) {
            if (isset($field['id']) && $field['id']) {
                continue;
            }
            ?>

            '<?= $field['name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return <?=\Paknahad\JsonApiBundle\Transformer::ResourceTransform($entity_var_name, $field)?>;
            },<?php
        }
        ?>

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultIncludedRelationships($<?= $entity_var_name ?>): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableRelations($<?= $entity_var_name ?>): array
    {
        return [<?php
    foreach ($associations as $association) {
        if (in_array($association['type'], $to_many_types)) {?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return ToManyRelationship::create()
                    ->setData($<?= $entity_var_name ?>-><?= $association['getter'] ?>(), InputOutputManager::makeTransformer(<?= $association['target_entity_name'] ?>ResourceTransformer::class));
            },<?php
        } else {?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return ToOneRelationship::create()
                    ->setData($<?= $entity_var_name ?>-><?= $association['getter'] ?>(), InputOutputManager::makeTransformer(<?= $association['target_entity_name'] ?>ResourceTransformer::class));
            },<?php
        }
    }?>

        ];
    }
}
