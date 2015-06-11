<?php
namespace Laurent\PiaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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

class PiaTacheController extends Controller
{
    private $authorization;
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
     *      "authorization"      = @DI\Inject("security.authorization_checker"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EntityManager $em,
        ObjectManager $om
    )
    {
        $this->authorization      = $authorization;
        $this->em                 = $em;
        $this->om                 = $om;
        $this->tachesRepo         = $om->getRepository('LaurentPiaBundle:Taches');
        $this->suivisRepo         = $om->getRepository('LaurentPiaBundle:Suivis');
        $this->actionsRepo        = $om->getRepository('LaurentPiaBundle:Actions');

    }

    /**
     * @EXT\Route("/", name="laurentPiaIndex")
     */
    public function indexAction()
    {
        $classes = $this->classeRepo->findAll();
        foreach ($classes as $classe){
            $groups[] = $classe->getGroup();
        }
        return $this->render('LaurentPiaBundle::PiaIndex.html.twig', array('groups' => $groups));
    }


    /**
     * @EXT\Route("/tache/add/{user}", name="laurentPiaTacheAdd", options = {"expose"=true})
     *
     * @EXT\Template("LaurentPiaBundle::TacheForm.html.twig")
     */
    public function PiaTacheAddAction(Request $request, User $user)
    {
        $this->checkOpen();
        $taches = new Taches();
        $form = $this->createForm(new TacheType, $taches);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $taches->setEleves($user);
                $this->em->persist($taches);
                $this->em->flush();
            }
            return new Response('success', 202);
        }
        return array('form' => $form->createView(), 'action' => $this->generateUrl('laurentPiaTacheAdd', array('user'=>$user->getId())));
    }

    /**
     * @EXT\Route("/tache/{tache}/edit", name="laurentPiaTacheEdit", options = {"expose"=true})
     *
     * @EXT\Template("LaurentPiaBundle::TacheForm.html.twig")
     */
    public function PiaTacheEditAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $form = $this->createForm(new TacheType, $tache);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($tache);
                $this->em->flush();
            }
            return new Response('success', 202);
        }

        return array('form' => $form->createView(), 'action' => $this->generateUrl('laurentPiaTacheEdit', array('tache'=>$tache->getId())));
    }

    /**
     * @EXT\Route("/tache/{tache}/delete", name="laurentPiaTacheDelete", options = {"expose"=true})
     *
     */
    public function PiaTacheDeleteAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $this->em->remove($tache);
        $this->em->flush();

        return new Response('success', 200);
    }

    /**
     * @EXT\Route("/tache/{tache}/close", name="laurentPiaTacheClose", options = {"expose"=true})
     *
     */
    public function PiaTacheCloseAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $tache->setFini(1);
        $this->em->persist($tache);
        $this->em->flush();

        return new Response('success', 200);
    }



    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }

}
