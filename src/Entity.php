<?php namespace Analogue\ORM;

use ArrayAccess;
use JsonSerializable;
use Analogue\ORM\System\EntityProxy;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Entity extends ValueObject implements Mappable, ArrayAccess, Jsonable, JsonSerializable, Arrayable {

	/**
	 * Entities Hidden Attributes, that will be discarded when converting
	 * the entity to Array/Json 
	 * (can include any embedded object's attribute)
	 * 
	 * @var array
	 */
	protected $hidden = [];

    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache = [];

    /**
     * Indicates whether attributes are snake cased on arrays.
     *
     * @var bool
     */
    public static $snakeAttributes = true;

    public function __construct()
    {
        $class = get_class($this);

        if (! isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }
    }

    public static function cacheMutatedAttributes($class)
    {
        $mutatedAttributes = [];

        // Here we will extract all of the mutated attributes so that we can quickly
        // spin through them after we export models to their array form, which we
        // need to be fast. This'll let us know the attributes that can mutate.
        foreach (get_class_methods($class) as $method) {
            if (strpos($method, 'Attribute') !== false &&
                preg_match('/^get(.+)Attribute$/', $method, $matches)) {
                /*if (static::$snakeAttributes) {
                    $matches[1] = Str::snake($matches[1]);
                }*/

                $mutatedAttributes[] = lcfirst($matches[1]);
            }
        }

        static::$mutatorCache[$class] = $mutatedAttributes;
    }

    /**
	 * Return the entity's attribute 
	 * @param  string $key 
	 * @return mixed
	 */
	public function __get($key)
	{
		/*if ($this->hasGetMutator($key))
		{
			$method = 'get'.$this->getMutatorMethod($key);

			return $this->$method($this->attributes[$key]);
		}*/

        /*if ($this->attributes[$key] instanceof EntityProxy)
		{
			$this->attributes[$key] = $this->attributes[$key]->load();
		}*/
        //Load relations by injected mapper
        if ( array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }

        if ($relation = $this->getEntityMap()->getRelation($key,$this))
        {
            $this->attributes[$key] = $relation;
            return $relation;
		}

        return null;
	}

	/**
     * Dynamically set attributes on the entity.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
    	if($this->hasSetMutator($key))
    	{
    		$method = 'set'.$this->getMutatorMethod($key);

    		$this->$method($value);
    	}
        else $this->attributes[$key] = $value;
    }

    /**
     * Is a getter method defined ?
     * 
     * @param  string  $key
     * @return boolean     
     */
    protected function hasGetMutator($key)
    {
    	//return method_exists($this, 'get'.$this->getMutatorMethod($key)) ? true : false;
        return in_array($key, static::$mutatorCache[get_class($this)]);
    }

    /**
     * Is a setter method defined ?
     * 
     * @param  string  $key
     * @return boolean     
     */
    protected function hasSetMutator($key)
    {
    	return method_exists($this, 'set'.$this->getMutatorMethod($key)) ? true : false;
    }

    protected function getMutatorMethod($key)
    {
    	return ucfirst($key).'Attribute';
    }

	/**
	 * Convert every attributes to value / arrays
	 * 
	 * @return array
	 */
	public function toArray()
	{	
        // First, call the trait method before filtering
        // with Entity specific methods
		$attributes = $this->attributesToArray($this->attributes);
		
		foreach($this->attributes as $key => $attribute)
		{
            if(in_array($key, $this->hidden))
			{
				unset($attributes[$key]);
				continue;
			}
			if($this->hasGetMutator($key))
			{
				$method = 'get'.$this->getMutatorMethod($key);
				$attributes[$key] = $this->$method($attribute);
			}
		}
		return $attributes;
	}

    public function __call($method, $parameters)
    {
        $innerParams = array_merge(array($method, $this),$parameters);//TODO: may be just + operatоr?
        return  call_user_func_array(array($this->EntityMap, 'getRelation'),  $innerParams);

        //TODO: add validation and throw exception
        /*
        $this->EntityMap->getRelation($method,$this,)
        if(! array_key_exists($method, $this->dynamicRelationships))
        {
            throw new Exception(get_class($this)." has no method $method");
        }

        // Add $this to parameters so the closure can call relationship method on the map.
        $parameters[] = $this;

        return  call_user_func_array(array($this->dynamicRelationships[$method], $parameters));
        */
    }
}
