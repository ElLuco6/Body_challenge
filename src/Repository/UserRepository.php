<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
    * @return User[] Returns an array of User objects
    */
    public function findRandomCoach(int $limit = 6)
    {   
        //Lister de manieres aléatoire 5 coaches sur la page d'acceuil avec la function RAND() avec une requête SQL classique
        // On se connecte
        $sth = $this->getEntityManager()->getConnection();
        // On fait notre requête
        $sql = 'SELECT * FROM `user` WHERE roles LIKE :role ORDER BY RAND() LIMIT :limit';
        // On la prépare
        $stmt = $sth->prepare($sql);
        // On associe nos valeurs aux parametres de la requetes
        $stmt->bindValue(':role', '%ROLE_COACH%');
        // On spécifie avec le \PDO::PARAM_INT les valeurs de sortie en INT
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        // On exécute notre requ$ete
        $stmt->execute();
        // On retourne tous les résultats
        return $stmt->fetchAll();
    }

    /**
    * @return User[] Returns an array of User objects
    */
    public function findCityCoach(string $city) {
        $sth = $this->getEntityManager()->getConnection();

        $sql = 'SELECT * FROM `user` WHERE city = :city';

        $stmt = $sth->prepare($sql);

        $stmt->bindValue(':city', $city, \PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll();

    }

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
