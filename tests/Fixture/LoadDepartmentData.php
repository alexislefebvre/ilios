<?php

namespace Tests\App\Fixture;

use App\Entity\Department;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadDepartmentData extends AbstractFixture implements
    ORMFixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('Tests\App\DataLoader\DepartmentData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new Department();
            $entity->setId($arr['id']);
            $entity->setTitle($arr['title']);
            $entity->setSchool($this->getReference('schools' . $arr['school']));
            $manager->persist($entity);
            $this->addReference('departments' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            'Tests\App\Fixture\LoadSchoolData'
        );
    }
}
