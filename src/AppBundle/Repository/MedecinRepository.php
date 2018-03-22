<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MedecinRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MedecinRepository extends EntityRepository
{
    /**
     * @param string $identifiant
     *
     * @return array
     */
    function findMedecinDuPatient($identifiant)
    {

        $qb = $this->createQueryBuilder('m');
        $qb->leftJoin('m.patientes', 'p')
            ->where('p.identifiant = :identifiant')
            ->setParameter('identifiant', $identifiant)
        ;

        return $qb->getQuery()->getResult();
    }
}