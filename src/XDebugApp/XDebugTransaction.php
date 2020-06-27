<?php declare(strict_types=1);

namespace Acme\XDebugApp;

class XDebugTransaction
{

    private string $id;

    private array $args = [];

    private string $cmd;

    private string $data = '';


    public function setId($id)
    {
        $this->id = (string) $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setData(string $data)
    {
        $this->data = $data;
    }

    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    public function setCommad(string $cmd)
    {
        $this->cmd = $cmd;
    }

    public function __toString(): string
    {
        $args = $this->args;

        $args[] = '-i';
        $args[] = $this->id;

        $cmd = $this->cmd . ' ' . implode(' ', $args);

        if ($this->data) {
            $cmd .= ' -- ' . base64_encode($this->data);
        }

        return $cmd;
    }
}
