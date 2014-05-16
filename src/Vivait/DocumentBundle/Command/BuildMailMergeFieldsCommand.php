<?php

namespace Vivait\DocumentBundle\Command;

use JMS\Serializer\SerializationContext;
use Vivait\DocumentBundle\Library\SimpleSerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
        $repo = $em->getRepository('VivaBravoBundle:Finance');
        $mailmerge = $this->getContainer()->get('vivait.document.mailmerge');

        $finance  = $repo->find(8986);
        $customer = $finance->getCustomer();
        $deal     = $finance->getDeal();

        $serializer = SimpleSerializerBuilder::build(true);
        $finance       = $serializer->serialize(
          $finance,
          'json',
          SerializationContext::create()->setSerializeNull(true)->setGroups(array('basic', 'finance'))
        );
        $deal       = $serializer->serialize(
          $deal,
          'json',
          SerializationContext::create()->setSerializeNull(true)->setGroups(array('basic', 'finance'))
        );
        $customer       = $serializer->serialize(
          $customer,
          'json',
          SerializationContext::create()->setSerializeNull(true)->setGroups(array('basic', 'finance'))
        );
        $data = [
          'deal' => $deal,
          'customer' => $customer,
          'finance' => $finance,
        ];
//        $data       = $mailmerge->extractRoots(
//          [
//            'deal' => $deal,
//            'customer' => $customer,
//            'finance' => $finance,
//          ],
//          ['customer', 'finance']
//        );

        $data = MailMergeService::flatten($data, '', '_');

        echo(implode(',', array_keys($data)) ."\n");

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