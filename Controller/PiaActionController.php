<?php
namespace Laurent\PiaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Persistence\ObjectManager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Laurent\PiaBundle\Entity\Suivis;
use Laurent\PiaBundle\Entity\Actions;
use Laurent\PiaBundle\Entity\Taches;
use Laurent\PiaBundle\Form\TacheType;
use Laurent\PiaBundle\Form\SuiviType;
use Laurent\PiaBundle\Form\ActionType;

class PiaActionController extends Controller
{
    private $sc;
    private $em;
    private $om;
    /** @var tachesRepository */
    private $tachesRepo;
    /** @var suivisRepository */
    private $suivisRepo;
    /** @var actionsRepository */
    private $actionsRepo;


    /**
     * @DI\InjectParams({
     *      "sc"                 = @DI\Inject("security.context"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */

    public function __construct(
        SecurityContextInterface $sc,
        EntityManager $em,
        ObjectManager $om
    )
    {
        $this->sc                 = $sc;
        $this->em                 = $em;
        $this->om                 = $om;
        $this->tachesRepo         = $om->getRepository('LaurentPiaBundle:Taches');
        $this->suivisRepo         = $om->getRepository('LaurentPiaBundle:Suivis');
        $this->actionsRepo        = $om->getRepository('LaurentPiaBundle:Actions');

    }

    /**
     * @EXT\Route("/actions/list/", name="laurentPiaActionList")
     */
    public function ActionListAction()
    {
        $actions = $this->actionsRepo->findAll();

        return $this->render('LaurentPiaBundle::PiaActionsList.html.twig', array('actions' => $actions));
    }


    /**
     * @EXT\Route("/action/create", name="laurentPiaActionCreate", options = {"expose"=true})
     *
     * @EXT\Template("LaurentPiaBundle::ActionForm.html.twig")
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
            return $this->redirect($this->generateUrl('laurentPiaActionList'));
        }
        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/action/{action}/edit", name="laurentPiaActionEdit", options = {"expose"=true})
     *
     * @EXT\Template("LaurentPiaBundle::ActionForm.html.twig")
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
            return $this->redirect($this->generateUrl('laurentPiaActionList'));
        }

        return array('form' => $form->createView(), 'action' => $this->generateUrl('laurentPiaActionEdit', array('action'=>$action->getId())));
    }

    /**
     * @EXT\Route("/action/{action}/delete", name="laurentPiaActionDelete", options = {"expose"=true})
     *
     */
    public function PiaTacheDeleteAction(Request $request, Actions $action)
    {
        $this->checkOpen();
        $this->em->remove($action);
        $this->em->flush();

        return $this->redirect($this->generateUrl('laurentPiaActionList'));
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }

}
