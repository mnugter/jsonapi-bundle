<?php
namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\ORM\EntityManager;
use Paknahad\JsonApiBundle\Hydrator\AbstractHydrator;
use Paknahad\JsonApiBundle\JsonApiStr;
use Paknahad\JsonApiBundle\ResourceTransformer\AbstractResourceTransformer;
use Paknahad\JsonApiBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InputOutputManager
{
    private static $entityManager;
    private static $token;

    public function __construct(EntityManager $entityManager, TokenStorageInterface $token)
    {
        self::$entityManager = $entityManager;
        self::$token = $token->getToken();
    }

    public function makeHydrator(string $action, string $hydratorClass): AbstractHydrator
    {
        return new $hydratorClass($action, self::$entityManager, self::$token, self::makeVoter($hydratorClass));
    }

    public static function makeTransformer(string $transformerClass): AbstractResourceTransformer
    {
        return new $transformerClass(self::$token, self::makeVoter($transformerClass));
    }

    public static function checkFilteringAuthority(string $fieldName, ?AbstractVoter $voter = null): bool
    {
        if (is_null($voter)) {
            return true;
        }

        return $voter->voteOnFiltering(self::$token, $fieldName);
    }

    protected static function makeVoter(string $fullClassName): ?AbstractVoter
    {
        $className = JsonApiStr::getClassShortName($fullClassName);

        if ($pos = strpos($className, 'Hydrator')) {
            $entity = substr($className, 0, $pos);
        } elseif ($pos = strpos($className, 'ResourceTransformer')) {
            $entity = substr($className, 0, $pos);
        } else {
            return null;
        }
        $voter = JsonApiStr::genVoterName($entity);

        if (class_exists($voter)) {
            return new $voter();
        }

        return null;
    }
}
