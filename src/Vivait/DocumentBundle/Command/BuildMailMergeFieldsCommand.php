<?php

namespace Vivait\DocumentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Viva\AuthBundle\Entity\Tenant;
use Viva\BravoBundle\Entity\Queue;
use Viva\BravoBundle\Entity\QueueDeal;
use Viva\BravoBundle\Entity\QueueLead;

class BuildMailMergeFieldsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
          ->setName('vivait:mailmerge:fields')
          ->setDescription('Builds a list of mail merge fields available');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $customer = $em->find(15130);

        JMS\Serializer\SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($customer, 'json');

        var_dump($jsonContent);
//
//        $entities = array();
//        $meta     = $em->getMetadataFactory()->getAllMetadata();
//        foreach ($meta as $m) {
////            /var_dump($m);
//
//            $entities[] = $m->getName();
//        }

        $output->writeln('done');
    }
}