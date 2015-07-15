<?php namespace Analogue\ORM;

use Analogue\ORM\System\ProxyInterface;

/**
 * Share behaviour of Entities/ValueObjects and allow
 * implementing mapping for custom classes 
 */
trait MappableTrait {

    /**
     * The Entity's Attributes
     * @var array
     */
    protected $attributes = [];

    /**
     * @var EntityMap
     */
    protected $EntityMap = null;

    /**
     * Method used by the mapper to set the object 
     * attribute raw values (hydration)
     * 
     * @param array $attributes 
     *
     * @return void
     */
    public function setEntityAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Method used by the mapper to get the 
     * raw object's values.
     *
     * @return array
     */
    public function getEntityAttributes()
    {
        return $this->attributes;
    }

    /**
     * Method used by the mapper to set raw
     * key-value pair
     * 
     * @param string $key  
     * @param string $value
     *
     * @return void
     */
    public function setEntityAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Method used by the mapper to get single
     * key-value pair
     * 
     * @param  string $key 
     * @return mixed|null
     */
    public function getEntityAttribute($key)
    {
        if(array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }
        else return null;
    }


    public function setEntityMap(EntityMap $map)
    {
        $this->EntityMap = $map;
    }

    /**
     * @return EntityMap
     */
    public function getEntityMap()
    {
        return $this->EntityMap;
    }

}
