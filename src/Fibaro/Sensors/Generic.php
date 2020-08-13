<?php

namespace Irekk\Fibaro\Sensors;

class Generic
{
    
    /**
     * @var numeric|string $id
     */
    public $id;
    
    /**
     * @var string $name
     */
    public $name;

    /**
     * @var numeric|string $value
     */
    public $value;

    /**
     * @var string $unit
     */
    public $unit;

    /**
     * @var string $room
     */
    public $room;

    /**
     * @var array $categories
     */
    public $categories = [];

    /**
     * Constructor
     * 
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->room = $data['roomID'];
        $this->categories = $data['properties']['categories'] ?? [];
        $this->value = $data['properties']['value'] ?? null;
        $this->unit = $data['properties']['unit'] ?? null;
    }
}