<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

class ArgumentDefinition extends \TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition
{
    /**
     * JSON schema definition
     *
     * @var string
     */
    protected $schema;

    /**
     * Constructor for this argument definition.
     *
     * @param string $name Name of argument
     * @param string $type Type of argument
     * @param string $description Description of argument
     * @param boolean $required TRUE if argument is required
     * @param mixed $defaultValue Default value
     * @param string $schema JSON schema definition
     */
    public function __construct($name, $type, $description, $required, $defaultValue = null, $schema = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
        $this->schema = $schema;
    }

    /**
     * Checks if the argument has a JSON schema definition
     *
     * @return boolean
     */
    public function hasSchema()
    {
        return isset($this->schema);
    }

    /**
     * Returns the JSON schema definition
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
