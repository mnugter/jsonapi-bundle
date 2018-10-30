<?php
namespace Paknahad\JsonApiBundle\Hydrator;

use Paknahad\JsonApiBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractHydrator extends BaseHydrator
{
    protected $objectManager;
    protected $voter;
    protected $token;
    protected $action;

    public function __construct(string $action, ObjectManager $objectManager, TokenInterface $token, ?AbstractVoter $voter = null)
    {
        $this->objectManager = $objectManager;
        $this->voter = $voter;
        $this->token = $token;
        $this->action = $action;
    }

    abstract protected function getAvailableAttributes($entity): array;

    abstract protected function getAvailableRelations($entity): array;

    /**
     * {@inheritdoc}
     */
    protected function getAttributeHydrator($entity): array
    {
        return $this->voter->voteOnInputFields(
            $this->action,
            $entity,
            $this->token,
            $this->getAvailableAttributes($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelationshipHydrator($entity): array
    {
        return $this->voter->voteOnInputRelations(
            $this->action,
            $entity,
            $this->token,
            $this->getAvailableRelations($entity)
        );
    }
}
