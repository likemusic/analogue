<?php namespace Analogue\ORM\System;

use Analogue\ORM\Mappable;
use Analogue\ORM\Relationships\Relationship;

abstract class Proxy implements ProxyInterface{

	/**
	 * The name of the relationship method handled by the proxy.
	 * 
	 * @var string
	 */
	protected $relation;

    /**
     * @var Relationship
     */
    protected $relatiionObject;

	/**
	 * Reference to parent entity object
	 *
	 * @var Mappable
	 */
	protected $entity;

	/**
	 * Lazy loaded relation flag
	 * 
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * @param string $relation 	relationship method handled by the proxy.
	 */
	public function __construct(Mappable $parentEntity, $relation, Relationship $relationObject)
	{
		$this->entity = $parentEntity;
		$this->relation = $relation;
        $this->relatiionObject = $relationObject;
	}

	/**
	 * Call the relationship method on the underlying entity map
	 * 
	 * @return Mappable|EntityCollection
	 */
	public function load()
	{
		//$entities = $this->query($this->entity, $this->relation)->getResults($this->relation);
        $entities = $this->relatiionObject->getResults($this->relation);

		$this->loaded = true;

		return $entities;
	}

	/**
     * Return true if the underlying relation has been lazy loaded
     * 
     * @return boolean
     */
	public function isLoaded()
	{
		return $this->loaded;
	}

	/**
	 * Return the Query Builder on the relation
	 * 
	 * @param  Mappable  $entity   
	 * @param  string    $relation 
	 * @return Query
	 */
	protected function query(Mappable $entity, $relation)
	{
		$entityMap = $this->getMapper($entity)->getEntityMap();

		return $entityMap->$relation($entity);
	}

	/**
	 * Get the mapper instance for the entity
	 * 
	 * @param  Mappable $entity 
	 * @return \Analogue\ORM\System\Mapper
	 */
	protected function getMapper(Mappable $entity)
	{
		return Manager::getMapper($entity);
	}
}