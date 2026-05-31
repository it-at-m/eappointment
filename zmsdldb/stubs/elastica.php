<?php

/**
 * Psalm stubs for the legacy Elastica 6 API used by zmsdldb.
 *
 * ruflin/elastica ^6 cannot be added to composer due to psr/log conflicts in the monorepo.
 */

namespace Elastica;

class Client
{
    /** @param array<string, mixed> $config */
    public function __construct(array $config = [])
    {
    }

    public function getIndex(string $name): Index
    {
        return new Index();
    }

    public function getStatus(): Status
    {
        return new Status();
    }
}

class Status
{
    /** @return list<string> */
    public function getIndexNames(): array
    {
        return [];
    }
}

class Index
{
    public function exists(): bool
    {
        return false;
    }

    /** @param array<string, mixed> $settings */
    public function create(array $settings = []): self
    {
        return $this;
    }

    public function getType(string $name): Type
    {
        return new Type();
    }

    public function refresh(): self
    {
        return $this;
    }

    public function addAlias(string $name, bool $replace = false): self
    {
        return $this;
    }

    public function getName(): string
    {
        return '';
    }

    public function getStatus(): Index\Status
    {
        return new Index\Status();
    }

    public function delete(): Response
    {
        return new Response();
    }
}

namespace Elastica\Index;

class Status
{
    /** @return list<string> */
    public function getAliases(): array
    {
        return [];
    }
}

namespace Elastica;

class Document
{
    /** @param array<string, mixed>|object $data */
    public function __construct(string $id, $data = [])
    {
    }
}

class Type
{
    /** @param list<Document> $docs */
    public function addDocuments(array $docs): Response
    {
        return new Response();
    }

    /** @return ResultSet */
    public function search($query, int $limit = 10)
    {
        return new ResultSet();
    }
}

class Response
{
}

class ResultSet implements \IteratorAggregate
{
    public function count(): int
    {
        return 0;
    }

    /** @return list<Result> */
    public function getResults(): array
    {
        return [];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([]);
    }
}

class Result
{
    /** @return array<string, mixed> */
    public function getData(): array
    {
        return [];
    }
}

class Query
{
    public function __construct($query = null)
    {
    }

    /** @param mixed $query */
    public static function create($query = null): self
    {
        return new self($query);
    }

    /** @param mixed $query */
    public function setQuery($query): self
    {
        return $this;
    }

    /** @param array<string, mixed> $sort */
    public function addSort(array $sort): self
    {
        return $this;
    }

    /** @param list<string> $fields */
    public function setSource(array $fields): self
    {
        return $this;
    }

    /** @param mixed $filter */
    public function setPostFilter($filter): self
    {
        return $this;
    }
}

namespace Elastica\Query;

class QueryString
{
    public function setQuery(string $query): self
    {
        return $this;
    }

    /** @param list<string> $fields */
    public function setFields(array $fields): self
    {
        return $this;
    }
}

class BoolQuery
{
    /** @param mixed $query */
    public function addMust($query): self
    {
        return $this;
    }

    /** @param mixed $query */
    public function addShould($query): self
    {
        return $this;
    }

    public function getQuery(): self
    {
        return $this;
    }
}

class Term
{
    /** @param array<string, mixed> $term */
    public function __construct(array $term = [])
    {
    }
}

class Terms
{
    /** @param list<string|int> $terms */
    public function __construct(string $field, array $terms = [])
    {
    }
}

class MatchAll
{
}

class Filtered
{
    public function __construct($query = null, $filter = null)
    {
    }

    public function getQuery(): BoolQuery
    {
        return new BoolQuery();
    }

    public function getFilter(): \Elastica\Filter\BoolFilter
    {
        return new \Elastica\Filter\BoolFilter();
    }
}

namespace Elastica\Filter;

class Terms
{
    /** @param list<string> $terms */
    public function __construct(string $field, array $terms = [])
    {
    }

    public function setExecution(string $execution): self
    {
        return $this;
    }
}

class Term
{
    /** @param array<string, mixed> $term */
    public function __construct(array $term = [])
    {
    }
}

class Ids
{
    /** @param string|list<string> $ids */
    public function setIds($ids): self
    {
        return $this;
    }
}

class BoolFilter
{
    /** @param mixed $filter */
    public function addMust($filter): self
    {
        return $this;
    }
}
