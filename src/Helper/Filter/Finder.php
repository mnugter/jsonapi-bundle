<?php
namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
use Paknahad\JsonApiBundle\Helper\InputOutputManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class Finder
 */
class Finder implements FinderInterface
{
    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FieldManager
     */
    protected $fieldManager;

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery(QueryBuilder $query): void
    {
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldManager(FieldManager $fieldManager): void
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery(): void
    {
        $filters = $this->request->get('filter', []);
        foreach ($filters as $field => $value) {
            $this->setCondition($field, $value);
        }
    }

    /**
     * @param string $field
     * @param string $value
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    protected function setCondition(string $field, string $value): void
    {
        $fieldMetaData = $this->fieldManager->addField($field);

        $this->checkAuthority($fieldMetaData);

        $this->query->andWhere(sprintf(
            '%s %s %s',
            $this->fieldManager->getQueryFieldName($field),
            $this->getOperator($fieldMetaData, $value),
            $this->setValue($value)
        ));
    }

    /**
     * Check user's authority for filtering this field
     *
     * @param array $metaData
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkAuthority(array $metaData): void
    {
        $isAuthorized = InputOutputManager::checkFilteringAuthority(
            $metaData['metadata']['fieldName'],
            $this->fieldManager->getVoter($metaData['entity'])
        );

        if (! $isAuthorized) {
            throw new AccessDeniedHttpException('You not authorized to filter this field: ' . $metaData['field']);
        }
    }

    /**
     * @param array       $fieldMetadata
     * @param string|null $value
     *
     * @return string
     */
    protected function getOperator(array $fieldMetadata, string &$value): string
    {
        if (strtolower($value) === 'null') {
            $value = null;

            return 'IS NULL';
        }

        if ($fieldMetadata['metadata']['type'] === 'string' && strpos($value, '%') !== false) {
            return 'LIKE';
        }

        return '=';
    }

    /**
     * Set value & return that parameter name
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function setValue($value): string
    {
        static $iterator = 1;

        if (null === $value) {
            return '';
        }

        $paramName = ':P'.$iterator++;

        $this->query->setParameter($paramName, $value);

        return $paramName;
    }
}
