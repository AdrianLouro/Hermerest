<?php
/**
 * Created by PhpStorm.
 * User: alour
 * Date: 09/04/2017
 * Time: 12:29
 */

namespace AppBundle\Facade;


use Doctrine\ORM\EntityManager;

abstract class AbstractFacade
{
    protected $entityManager;
    protected $entityName;

    public function __construct(EntityManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    public function create($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function edit()
    {
        $this->entityManager->flush();
    }

    public function remove($entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function find($id)
    {
        return $this->entityManager->getRepository($this->entityName)->find($id);
    }

    public function findAll()
    {
        return $this->entityManager->getRepository($this->entityName)->findAll();
    }
}