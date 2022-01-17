<?php

namespace BO\Zmsclient\GraphQL;

class GraphQLInterpreter implements \JsonSerializable
{
    protected $gqlString;

    public function __construct($gqlString)
    {
        $this->gqlString = $gqlString;
    }

    protected function getGraphInterpretation()
    {
        if (!preg_match_all('#\w+|[{}]#', $this->gqlString, $parts)) {
            throw new GraphQLException("No content for graph");
        }
        $parts = $parts[0];
        if ($parts[0] != '{' || end($parts) != '}') {
            throw new GraphQLException("No valid graphql");
        }
        $node = new GraphQLNode();
        foreach ($parts as $part) {
            if ($part == '{') {
                $node = $node->addSubNode();
            } elseif ($part == '}') {
                $node = $node->getParent();
            } else {
                $node = $node->addElement($part);
            }
        }
        $node = $node->getRealRoot();
        return $node;
    }

    protected function reduceData(): self
    {
        $graph = $this->getGraphInterpretation();
        $reducedData = $graph->getNodesFromIterable($this->data);
        if (isset($this->data[0]['$schema'])) {
            $schema = $this->data[0]['$schema'];
            $reducedData[0]['$schema'] = $schema;
        } elseif (isset($this->data['$schema'])) {
            $schema = $this->data['$schema'];
            $reducedData['$schema'] = $schema;
        }
        $this->data = $reducedData;

        return $this;
    }

    public function setJson($json): self
    {
        $this->data = json_decode($json, true);
        $this->reduceData();
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
