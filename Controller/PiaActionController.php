<?php

namespace FormaLibre\PiaBundle\Controller;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\PiaBundle\Entity\Actions;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Claroline\MessageBundle\Manager\MessageManager;
use FormaLibre\PiaBundle\Form\ActionType;

class PiaActionController extends Controller
{
    private $authorization;
    private $em;
    private $om;
    private $messageManager;

    /** @var actionsRepository */
    private $actionsRepo;
    /** @var suivisRepository */
    private $suivisRepo;
    /** @var tachesRepository */
    private $tachesRepo;


    /**
     * @DI\InjectParams({
     *      "authorization"      = @DI\Inject("security.authorization_checker"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "messageManager"     = @DI\Inject("claroline.manager.message_manager"),
     * })
     */

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EntityManager $em,
        ObjectManager $om,
        MessageManager $messageManager
    )
    {
        $this->authorization = $authorization;
        $this->em = $em;
        $this->om = $om;
        $this->messageManager = $messageManager;

        $this->actionsRepo = $om->getRepository('FormaLibrePiaBundle:Actions');
        $this->suivisRepo = $om->getRepository('FormaLibrePiaBundle:Suivis');
        $this->tachesRepo = $om->getRepository('FormaLibrePiaBundle:Taches');

    }

    /**
     * @EXT\Route("/actions/list/", name="formalibrePiaActionList")
     */
    public function ActionListAction()
    {
        $actions = $this->actionsRepo->findAll();

        return $this->render('FormaLibrePiaBundle::PiaActionsList.html.twig', array('actions' => $actions));
    }


    /**
     * @EXT\Route("/action/create", name="formalibrePiaActionCreate", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibrePiaBundle::ActionForm.html.twig")
     */
    public function CreateAction(Request $request)
    {
        $this->checkOpen();
        $action = new Actions();
        $form = $this->createForm(new ActionType, $action);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($action);
                $this->em->flush();
            }
            return $this->redirect($this->generateUrl('formalibrePiaActionList'));
        }
        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/action/{action}/edit", name="formalibrePiaActionEdit", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibrePiaBundle::ActionForm.html.twig")
     */
    public function PiaActionEditAction(Request $request, Actions $action)
    {
        $this->checkOpen();
        $form = $this->createForm(new ActionType(), $action);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($action);
                $this->em->flush();
            }
            return $this->redirect($this->generateUrl('formalibrePiaActionList'));
        }

        return array('form' => $form->createView(), 'action' => $this->generateUrl('formalibrePiaActionEdit', array('action'=>$action->getId())));
    }

    /**
     * @EXT\Route("/action/{action}/delete", name="formalibrePiaActionDelete", options = {"expose"=true})
     *
     */
    public function PiaTacheDeleteAction(Request $request, Actions $action)
    {
        $this->checkOpen();
        $this->em->remove($action);
        $this->em->flush();

        return $this->redirect($this->generateUrl('formalibrePiaActionList'));
    }

    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }

}
