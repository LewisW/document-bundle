<?php

namespace Vivait\DocumentBundle\Command;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Viva\AuthBundle\Entity\Tenant;
use Viva\BravoBundle\Entity\Queue;
use Viva\BravoBundle\Entity\QueueDeal;
use Viva\BravoBundle\Entity\QueueLead;
use Vivait\DocumentBundle\Services\MailMergeService;

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

        $customer = $em->getRepository('VivaBravoBundle:Deal')->find(12720);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($customer, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups(array('basic', 'deal')));
        $deal = json_decode($jsonContent, JSON_OBJECT_AS_ARRAY);

        echo(implode(',', array_keys(MailMergeService::flatten(['deal' => $deal], '', ' '))));

        /*
         * if ($v === null) {
            $reader = new AnnotationReader();
            $annotations = $reader->getPropertyAnnotations($metadata->reflection);

            foreach ($annotations as $annotation) {
                if ($annotation instanceOf \Doctrine\ORM\Mapping\OneToOne) {
                    $ref = new \ReflectionClass((strpos($annotation->targetEntity, '\\') === false ? 'Viva\BravoBundle\Entity\\' : '') . $annotation->targetEntity);
                    if ($ref->isAbstract() || $ref->isInterface()) {
                        foreach(get_declared_classes() as $class){
                            if(is_subclass_of($class, $annotation->targetEntity)) {
                                $ref = new \ReflectionClass($class);

                                if (count($ref->getConstructor()->getParameters())) {
                                    continue 2;
                                }

                                break;
                            }
                        }
                    }

                    $this->navigator->accept($ref->newInstance(), $metadata->type, $context);
                }
            }
        }
         * */

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