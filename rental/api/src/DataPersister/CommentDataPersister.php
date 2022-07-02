<?php

namespace App\DataPersister;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

/**
 *
 */
class CommentDataPersister implements ContextAwareDataPersisterInterface
{
    private $_entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->_entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Comment;
    }

    /**
     * @param Comment $data
     */
    public function persist($data, array $context = [])
    {
        ini_set("precision", 3);
        $this->_entityManager->persist($data);
        $this->_entityManager->flush();

        $product = $data->getProduct();
        $comments = $product->getComments();

        $sum = 0;
        $nb = 0;

        foreach ($comments as $comment){
            if($comment->getRating()){
                $sum = $sum + $comment->getRating();
                $nb++;
            }
        }
        $product->setAverageRatings($nb == 0 ? 0 : $sum/$nb);
        $product->setNumbersOfRatings($nb);

        $this->_entityManager->persist($product);
        $this->_entityManager->flush();

    }

    /**
     * {@inheritdoc}
     */
    public function remove($data, array $context = [])
    {
        $this->_entityManager->remove($data);
        $this->_entityManager->flush();
    }
}
