<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderArticle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('en_US');



        for ($i = 0;$i < 1000; $i++) {

            // 1. Создаем первый заказ (физическое лицо)
            $order1 = new Order();
            $order1->setHash(md5($faker->unique()->uuid));
            $order1->setUserId($faker->numberBetween(1, 1000));
            $order1->setToken(sha1($faker->uuid));
            $order1->setNumber('100000' . $i);
            $order1->setStatus($faker->numberBetween(1, 4));
            $order1->setEmail($faker->unique()->safeEmail);
            $order1->setVatType($faker->numberBetween(1, 4)); 
            $order1->setDiscount($faker->numberBetween(0, 15));
            $order1->setDelivery($faker->randomFloat(2, 50, 300));
            $order1->setDeliveryType($faker->numberBetween(0, 1)); 
            $deliveryMin = $faker->dateTimeBetween('+5 days', '+10 days');
            $order1->setDeliveryTimeMin($deliveryMin);
            $order1->setDeliveryTimeMax((clone $deliveryMin)->modify('+' . $faker->numberBetween(2, 5) . ' days'));
            $order1->setDeliveryIndex($faker->postcode);
            $order1->setDeliveryCountry(380); // Италия
            $order1->setDeliveryRegion($faker->state);



            $order1->setDeliveryCity($faker->city);
            $order1->setDeliveryAddress($faker->streetAddress);
            $order1->setClientName($faker->firstName);
            $order1->setClientSurname($faker->lastName);
            $order1->setPayType(1); 
            $order1->setLocale('it');
            $order1->setCurrency('EUR');
            $order1->setMeasure('m');
            $order1->setName($faker->words(4, true));
            $order1->setDescription($faker->realText(150));
            $order1->setCreateDate($faker->dateTimeBetween('-600 days', '-1 day'));
            $order1->setStep(1);
            $order1->setAddressEqual($faker->boolean(90));
            $order1->setAcceptPay($faker->boolean(70));
            $order1->setWeightGross($faker->randomFloat(2, 50, 500));

    


            $articlesCount = $faker->numberBetween(1, 3);
    
            for ($j = 0; $j < $articlesCount; $j++) {
                $article = new OrderArticle();
                $article->setArticleId($faker->numberBetween(100, 999));
                $article->setAmount($faker->randomFloat(1, 5, 50));
                
                $price = $faker->randomFloat(2, 20, 150);
                $article->setPrice($price);
                $article->setPriceEur($price);
                
                $article->setCurrency('EUR');
                $article->setMeasure('mq');
                $article->setWeight($faker->randomFloat(1, 5, 25));
                $article->setMultiplePallet(1);
                $article->setPackagingCount($faker->randomFloat(2, 1, 2));
                $article->setPallet($faker->randomFloat(1, 30, 80));
                $article->setPackaging($faker->randomFloat(2, 1, 2));
                $article->setSwimmingPool($faker->boolean(30)); 
                
                $order1->addArticle($article);
            
            }


            $manager->persist($order1);
        }

    
        $manager->flush();
    }
}
