<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Model;

class Prop
{
    public string $kind;

    public string $table;

    public string $field;

    public string $value;

    /**
     * Gets kind.
     *
     * @api
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * Sets kind.
     *
     * @api
     */
    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }

    /**
     * Gets table.
     *
     * @api
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Sets table.
     *
     * @api
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * Gets field.
     *
     * @api
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Sets field.
     *
     * @api
     */
    public function setField(string $field): void
    {
        $this->field = $field;
    }

    /**
     * Gets value.
     *
     * @api
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets value.
     *
     * @api
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
