<?php

	namespace Vivait\DocumentBundle\Controller;

	use Doctrine\ORM\EntityManager;
	use Symfony\Component\HttpFoundation\Request;
	use Vivait\BootstrapBundle\Controller\Controller;
	use Vivait\DocumentBundle\Entity\Bundle;
	use JMS\DiExtraBundle\Annotation as DI;
	use Vivait\DocumentBundle\Entity\BundleRepository;
	use Vivait\DocumentBundle\Event\BundleEvent;

	class BundleController extends Controller {

		/**
		 * @var BundleRepository
		 */
		protected $repository;

		/**
		 * @DI\InjectParams({
		 *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
		 * })
		 */
		function __construct(EntityManager $em) {
			$this->repository = $em->getRepository('VivaitDocumentBundle:Bundle');
		}

		public function indexAction() {
			return $this->render('VivaitDocumentBundle:Maintenance:bundles.html.twig', array(
				'db' => $this->repository->findAll()
			));
		}

		public function newAction(Request $request) {
			$obj = $this->repository->create();
			return $this->editAction($request, $obj, true);
		}

		public function editAction(Request $request, Bundle $bundle, $is_new = false) {
			$form = $this->createForm('bundle', $bundle);
			$form->handleRequest($request);

			if($form->isValid()) {
				if (!$is_new && $form->get('delete')->isClicked()) {
					$this->delete($bundle);
					return $this->redirectBack($request);
				}

				$this->repository->save($bundle);

				$dispatcher = $this->container->get('event_dispatcher');
				$dispatcher->dispatch(BundleEvent::EVENT_ENTITY_MODIFIED, new BundleEvent($bundle));

				return $this->redirectBack($request);
			}

			return $this->render('VivaitBootstrapBundle:Default:form.html.twig', [
				'form' => [
					'title' => 'Add/Edit document',
					'form'  => $form->createView()
				]
			]);
		}

		private function delete(Bundle $bundle) {
			$this->repository->delete($bundle);

			$dispatcher = $this->container->get('event_dispatcher');
			$dispatcher->dispatch(BundleEvent::EVENT_ENTITY_DELETED, new BundleEvent($bundle));
		}
	}
