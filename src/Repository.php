<?php namespace Analogue\ORM;

use Exception;
use Analogue\ORM\Mappable;
use Analogue\ORM\EntityMap;
use Analogue\ORM\System\Mapper;
use Analogue\ORM\System\Manager;
use InvalidArgumentException;

class Repository {

	/**
	 * The mapper object for the corresponding entity
	 * 
	 * @var \Analogue\ORM\System\Mapper
	 */
	protected $mapper;

    protected $MappedClassName;
	/**
	 * To build a repository, either provide :
	 * 
	 * - Mappable object's class name as a string
	 * - Mappable object instance
	 * - Instance of mapper
	 * 
	 * @param Mapper|Mappable|string $mapper 
	 * @param EntityMap 			 $entityMap (optionnal)
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function __construct($mapper, EntityMap $entityMap = null)
	{
		if($mapper instanceof Mappable || is_string($mapper))
		{
			$this->mapper = Manager::getMapper($mapper, $entityMap);
		}
		else if($mapper instanceof Mapper)
		{
			$this->mapper = $mapper;
		}
		else new InvalidArgumentException('Repository class constuctor need a valid Mapper or Mappable object.');

        $this->MappedClassName = $this->mapper->getEntityMap()->getClass();
    }

    public function __sleep()
    {
        $Fields = get_object_vars($this);
        return [
            'ItemClassName',
            'VirtualRootCategoryId',
            'MappedClassName'
            #'mapper'
        ];
    }

    public function __wakeup()
    {
        $this->mapper = Manager::getMapper($this->MappedClassName);
    }

	/**
	 * Return all Entities from database
	 *  
	 * @return \Analogue\ORM\EntityCollection
	 */
	public function all()
	{
		return $this->mapper->get();
	}
	
	/**
	 * Fetch a record from the database
	 * @param  integer $id 
	 * @return \Analogue\ORM\Mappable
	 */
	public function find($id)
	{
		return $this->mapper->find($id);
	}

	/**
	 * Get the first entity matching the given attributes.
	 *
	 * @param  array  $attributes
	 * @return \Analogue\ORM\Mappable|null
	 */
	public function firstMatching(array $attributes)
	{
		return $this->mapper->where($attributes)->first();
	}

	/**
	 * Return all the entities matching the given attributes
	 *
	 * @param array $attributes
	 * @return \Analogue\ORM\EntityCollection
	 */
	public function allMatching(array $attributes)
	{
		return $this->mapper->where($attributes)->get();
	}

	/**
	 * Return a paginator instance on the EntityCollection
	 * 
	 * @param  int $perPage number of item per page (fallback on default setup in entity map)
	 * @return 
	 */
	public function paginate($perPage = null)
	{
		return $this->mapper->paginate($perPage);
	}

	/**
	 * Delete an entity or an entity collection from the database
	 * 
	 * @param  Mappable|Collection $entity 
	 * @return null
	 */
	public function delete($entity)
	{
		return $this->mapper->delete($entity);
	}

	/**
	 * Persist an entity or an entity collection in the database.
	 * 
	 * @param  Mappable|Collection|array $entity 
	 * @return Mappable|Collection|array
	 */
	public function store($entity)
	{
		return $this->mapper->store($entity);	
	}

	/**
	 * Make custom mapper custom commands available in repository
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if($this->mapper->hasCustomCommand($method))
		{
			call_user_func_array(array($this->mapper, $method), $parameters);
		}
		else 
		{
			throw new Exception("No method $method on ".get_class($this));
		}
	}
}
