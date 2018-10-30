<?php
namespace Paknahad\JsonApiBundle\ResourceTransformer;

use Paknahad\JsonApiBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer as Base;

abstract class AbstractResourceTransformer extends Base
{
    protected $voter;
    protected $token;

    public function __construct(TokenInterface $token, ?AbstractVoter $voter = null)
    {
        $this->voter = $voter;
        $this->token = $token;
    }

    abstract protected function getAvailableAttributes($entity): array;

    abstract protected function getAvailableRelations($entity): array;

    /**
     * {@inheritdoc}
     */
    public function getAttributes($entity): array
    {
        return $this->voter->voteOnOutputFields($entity, $this->token, $this->getAvailableAttributes($entity));
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($entity): array
    {
        return $this->voter->voteOnOutputRelations($entity, $this->token, $this->getAvailableRelations($entity));
    }
}
