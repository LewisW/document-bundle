<?php

	namespace Vivait\DocumentBundle\Controller;

	use Symfony\Component\HttpFoundation\Request;
	use Vivait\BootstrapBundle\Controller\Controller;
	use Vivait\DocumentBundle\Entity\Letter;
	use JMS\DiExtraBundle\Annotation as DI;
	use Vivait\DocumentBundle\Event\LetterEvent;

	class LetterController extends Controller {

		/**
		 * @var LetterRepository
		 */
		protected $repository;

		/**
		 * @DI\InjectParams({
		 *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
		 * })
		 */
		function __construct($em) {
			$this->repository = $em->getRepository('VivaitDocumentBundle:Letter');
		}

		public function indexAction() {
			return $this->render('VivaApolloBundle:Maintenance:letters.html.twig', array(
				'db' => $this->repository->findAll()
			));
		}

		public function newAction(Request $request) {
			$obj = $this->repository->create();
			return $this->editAction($request, $obj, true);
		}

		public function editAction(Request $request, Letter $letter, $is_new = false) {
			$form = $this->createForm('letter', $letter);
			$form->handleRequest($request);

			if($form->isValid()) {
				if (!$is_new && $form->get('delete')->isClicked()) {
					$this->delete($letter);
					return $this->redirectBack($request);
				}

				$this->repository->save($letter);

				$dispatcher = $this->container->get('event_dispatcher');
				$dispatcher->dispatch(LetterEvent::EVENT_ENTITY_MODIFIED, new LetterEvent($letter));

				return $this->redirectBack($request);
			}

			return $this->render('VivaitBootstrapBundle:Default:form.html.twig', [
				'form' => [
					'title' => 'Add/Edit document',
					'form'  => $form->createView()
				]
			]);
		}

		private function delete(Letter $letter) {
			$this->repository->delete($letter);

			$dispatcher = $this->container->get('event_dispatcher');
			$dispatcher->dispatch(LetterEvent::EVENT_ENTITY_DELETED, new LetterEvent($letter));
		}
	}
